<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

use Develate\CodexCli\Value\Usage;

/** Last-response and cumulative token-usage snapshots from the app server. */
final readonly class ThreadTokenUsageUpdated implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $threadId,
        public string $turnId,
        public Usage $last,
        public Usage $total,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
