<?php

declare(strict_types=1);

namespace Develate\CodexCli;

use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\ErrorEvent;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\Reasoning;
use Develate\CodexCli\Event\TodoList;
use Develate\CodexCli\Event\TurnFailed;
use Develate\CodexCli\Value\RunMetadata;
use Develate\CodexCli\Value\Usage;

final readonly class TurnResult
{
    /** @param list<CodexEvent> $items */
    public function __construct(
        public string $threadId,
        public string $response,
        public array $items,
        public Usage $usage,
        public RunMetadata $metadata,
        public int $exitCode,
    ) {
    }

    /** @return list<CommandExecution> */
    public function commands(): array
    {
        return $this->ofType(CommandExecution::class);
    }

    /** @return list<FileChange> */
    public function fileChanges(): array
    {
        return $this->ofType(FileChange::class);
    }

    /** @return list<Reasoning> */
    public function reasoning(): array
    {
        return $this->ofType(Reasoning::class);
    }

    public function todoList(): ?TodoList
    {
        $lists = $this->ofType(TodoList::class);

        return $lists === [] ? null : $lists[array_key_last($lists)];
    }

    /** @return list<ErrorEvent|TurnFailed> */
    public function errors(): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (CodexEvent $event): bool => $event instanceof ErrorEvent || $event instanceof TurnFailed,
        ));
    }

    /** @template T of CodexEvent @param class-string<T> $class @return list<T> */
    private function ofType(string $class): array
    {
        return array_values(array_filter($this->items, static fn (CodexEvent $event): bool => $event instanceof $class));
    }
}
