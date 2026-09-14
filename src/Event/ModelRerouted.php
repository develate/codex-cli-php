<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

/** A provider-confirmed model reroute for one app-server turn. */
final readonly class ModelRerouted implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $threadId,
        public string $turnId,
        public string $fromModel,
        public string $toModel,
        public ?string $reason,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
