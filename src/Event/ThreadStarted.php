<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

final readonly class ThreadStarted implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(public string $threadId, private array $raw)
    {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
