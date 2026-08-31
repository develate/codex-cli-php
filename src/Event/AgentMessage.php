<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

final readonly class AgentMessage implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $id,
        public string $text,
        public string $phase,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
