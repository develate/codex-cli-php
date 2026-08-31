<?php

declare(strict_types=1);

namespace Develate\CodexCli;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\ThreadStarted;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\TurnStarted;
use Develate\CodexCli\Value\RunMetadata;
use Develate\CodexCli\Value\Usage;

final class TurnResultBuilder
{
    private string $threadId;
    private Usage $usage;

    /** @var list<CodexEvent> */
    private array $events = [];

    public function __construct(string $threadId = '')
    {
        $this->threadId = $threadId;
        $this->usage = new Usage();
    }

    public function add(CodexEvent $event): void
    {
        if ($event instanceof ThreadStarted) {
            $this->threadId = $event->threadId;
        }
        if ($event instanceof TurnCompleted) {
            $this->usage = $event->usage;
        }

        $this->events[] = $event;
    }

    public function build(RunMetadata $metadata, int $exitCode): TurnResult
    {
        $response = '';
        foreach ($this->events as $event) {
            if ($event instanceof AgentMessage && ($response === '' || $event->phase === 'completed')) {
                $response = $event->text;
            }
        }

        return new TurnResult(
            threadId: $this->threadId,
            response: $response,
            items: $this->latestItems(),
            usage: $this->usage,
            metadata: $metadata,
            exitCode: $exitCode,
        );
    }

    /** @return list<CodexEvent> */
    private function latestItems(): array
    {
        $latest = [];
        $unkeyed = [];
        foreach ($this->events as $event) {
            if ($event instanceof ThreadStarted || $event instanceof TurnStarted || $event instanceof TurnCompleted) {
                continue;
            }
            $id = property_exists($event, 'id') ? (string) $event->id : '';
            if ($id === '') {
                $unkeyed[] = $event;
                continue;
            }

            $suffix = $event instanceof FileChange ? ':'.$event->path : '';
            $latest[$event::class.':'.$id.$suffix] = $event;
        }

        return array_values([...$unkeyed, ...$latest]);
    }
}
