<?php

declare(strict_types=1);

namespace Develate\CodexCli;

use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Exception\ProcessFailed;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Transport\SteerableTransport;
use Develate\CodexCli\Transport\Transport;
use Develate\CodexCli\Value\RunMetadata;
use Develate\CodexCli\Value\Sandbox;

final class Thread
{
    private ?TurnResult $lastResult = null;

    private bool $running = false;

    /** @var list<callable(CodexEvent): void> */
    private array $listeners = [];

    /**
     * @param list<string> $writableDirectories
     */
    public function __construct(
        private readonly Transport $transport,
        private readonly string $codexVersion,
        private readonly string $cwd,
        private readonly ?string $model,
        private readonly Sandbox $sandbox,
        private readonly array $writableDirectories = [],
        private readonly ?float $timeout = null,
        private ?string $threadId = null,
        private RunMode $nextMode = RunMode::Start,
    ) {
    }

    public function id(): ?string
    {
        return $this->threadId;
    }

    /**
     * @param array<string, mixed>|null $schema
     * @param list<string> $images
     */
    public function run(string $prompt, ?array $schema = null, array $images = []): TurnResult
    {
        foreach ($this->stream($prompt, $schema, $images) as $_) {
        }

        return $this->result();
    }

    /**
     * @param array<string, mixed>|null $schema
     * @param list<string> $images
     * @return \Generator<int, CodexEvent, void, void>
     */
    public function stream(string $prompt, ?array $schema = null, array $images = []): \Generator
    {
        if ($this->running) {
            throw new \LogicException('This thread already has a turn running.');
        }

        $this->running = true;
        try {
            $builder = new TurnResultBuilder($this->threadId ?? '');
            $stream = $this->transport->stream(new RunRequest(
                mode: $this->nextMode,
                threadId: $this->threadId,
                prompt: $prompt,
                cwd: $this->cwd,
                model: $this->model,
                sandbox: $this->sandbox,
                writableDirectories: $this->writableDirectories,
                images: $images,
                schema: $schema,
                timeout: $this->timeout,
            ));

            foreach ($stream as $event) {
                $builder->add($event);
                foreach ($this->listeners as $listener) {
                    $listener($event);
                }
                yield $event;
            }

            $process = $stream->getReturn();
            $this->lastResult = $builder->build(new RunMetadata(
                codexVersion: $this->codexVersion,
                requestedModel: $this->model,
                cwd: $this->cwd,
            ), $process->exitCode);

            if ($this->lastResult->threadId !== '') {
                $this->threadId = $this->lastResult->threadId;
                $this->nextMode = RunMode::Resume;
            }

            if ($process->exitCode !== 0) {
                throw new ProcessFailed($process->exitCode, $process->stderr);
            }
        } finally {
            $this->running = false;
        }
    }

    /** @param list<string> $images */
    public function steer(string $prompt, array $images = []): string
    {
        if (!$this->running || !$this->transport instanceof SteerableTransport) {
            throw new \LogicException('Steering requires an active interactive turn.');
        }

        return $this->transport->steer($prompt, $images);
    }

    public function result(): TurnResult
    {
        return $this->lastResult ?? throw new \LogicException('This thread has no completed turn result yet.');
    }

    public function fork(): self
    {
        if ($this->threadId === null || $this->threadId === '') {
            throw new \LogicException('A thread can only be forked after it has an id.');
        }

        return new self(
            transport: $this->transport,
            codexVersion: $this->codexVersion,
            cwd: $this->cwd,
            model: $this->model,
            sandbox: $this->sandbox,
            writableDirectories: $this->writableDirectories,
            timeout: $this->timeout,
            threadId: $this->threadId,
            nextMode: RunMode::Fork,
        );
    }

    /** @param callable(CodexEvent): void $listener */
    public function onEvent(callable $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    /** @param callable(CommandExecution): void $listener */
    public function onCommandStarted(callable $listener): self
    {
        return $this->onEvent(static function (CodexEvent $event) use ($listener): void {
            if ($event instanceof CommandExecution && $event->phase === 'started') {
                $listener($event);
            }
        });
    }

    /** @param callable(CommandExecution): void $listener */
    public function onCommandCompleted(callable $listener): self
    {
        return $this->onEvent(static function (CodexEvent $event) use ($listener): void {
            if ($event instanceof CommandExecution && $event->phase === 'completed') {
                $listener($event);
            }
        });
    }
}
