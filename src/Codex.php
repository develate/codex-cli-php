<?php

declare(strict_types=1);

namespace Develate\CodexCli;

use Develate\CodexCli\Exception\CodexNotFound;
use Develate\CodexCli\Exception\ProcessFailed;
use Develate\CodexCli\Process\CodexExecutable;
use Develate\CodexCli\Transport\AppServerTransport;
use Develate\CodexCli\Transport\ExecTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\Transport;
use Develate\CodexCli\Value\Account;
use Develate\CodexCli\Value\Quota;
use Develate\CodexCli\Value\Sandbox;
use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailed;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyRuntimeException;
use Symfony\Component\Process\Process;

final class Codex
{
    private readonly Transport $execTransport;
    private readonly AppServerTransport $appServerTransport;
    private ?string $versionCache = null;
    private ?string $defaultCwd = null;
    private Sandbox $defaultSandbox = Sandbox::ReadOnly;

    public function __construct(
        private readonly string $binary = 'codex',
        ?Transport $execTransport = null,
        ?AppServerTransport $appServerTransport = null,
        private readonly ?float $timeout = null,
    ) {
        $this->execTransport = $execTransport ?? new ExecTransport($binary);
        $this->appServerTransport = $appServerTransport ?? new AppServerTransport($binary);
    }

    /**
     * @param list<string> $writableDirectories
     */
    public function thread(
        ?string $cwd = null,
        ?string $model = null,
        ?Sandbox $sandbox = null,
        array $writableDirectories = [],
    ): Thread {
        return new Thread(
            transport: $this->execTransport,
            codexVersion: $this->version(),
            cwd: $this->resolveCwd($cwd),
            model: $model,
            sandbox: $sandbox ?? $this->defaultSandbox,
            writableDirectories: $writableDirectories,
            timeout: $this->timeout,
        );
    }

    public function resume(string $id, ?string $cwd = null, ?string $model = null): Thread
    {
        return new Thread(
            transport: $this->execTransport,
            codexVersion: $this->version(),
            cwd: $this->resolveCwd($cwd),
            model: $model,
            sandbox: $this->defaultSandbox,
            timeout: $this->timeout,
            threadId: $id,
            nextMode: RunMode::Resume,
        );
    }

    public function in(string $cwd): self
    {
        $clone = clone $this;
        $clone->defaultCwd = $cwd;

        return $clone;
    }

    public function dangerouslyAllowFullAccess(): self
    {
        $clone = clone $this;
        $clone->defaultSandbox = Sandbox::FullAccess;

        return $clone;
    }

    /**
     * @param array<string, mixed>|null $schema
     * @param list<string> $images
     */
    public function run(string $prompt, ?array $schema = null, array $images = []): TurnResult
    {
        return $this->thread()->run($prompt, $schema, $images);
    }

    public function quota(): Quota
    {
        return $this->appServerTransport->quota();
    }

    public function account(): Account
    {
        return $this->appServerTransport->account();
    }

    public function version(): string
    {
        if ($this->versionCache !== null) {
            return $this->versionCache;
        }

        CodexExecutable::assertAvailable($this->binary);
        try {
            $process = new Process([$this->binary, '--version'], timeout: 5.0);
            $process->mustRun();
        } catch (SymfonyProcessFailed $exception) {
            throw new ProcessFailed($exception->getProcess()->getExitCode() ?? 1, $exception->getProcess()->getErrorOutput());
        } catch (SymfonyRuntimeException $exception) {
            throw new CodexNotFound(sprintf('Codex binary "%s" was not found.', $this->binary), 0, $exception);
        }

        $output = trim($process->getOutput());
        if (preg_match('/(?:codex-cli\s+)?([0-9]+(?:\.[0-9]+){1,3}(?:[-+][^\s]+)?)/', $output, $matches) === 1) {
            return $this->versionCache = $matches[1];
        }

        return $this->versionCache = $output;
    }

    private function resolveCwd(?string $cwd): string
    {
        $resolved = $cwd ?? $this->defaultCwd ?? getcwd();
        if (!is_string($resolved) || $resolved === '') {
            throw new \RuntimeException('Unable to determine the working directory.');
        }

        return $resolved;
    }
}
