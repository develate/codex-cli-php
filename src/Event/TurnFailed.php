<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

final readonly class TurnFailed implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $message,
        private array $raw,
        public ?string $threadId = null,
        public ?string $turnId = null,
    )
    {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
