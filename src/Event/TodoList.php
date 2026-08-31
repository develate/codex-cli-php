<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

final readonly class TodoList implements CodexEvent
{
    /**
     * @param list<array<string, mixed>> $items
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public array $items,
        public string $phase,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
