<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

use Develate\CodexCli\Value\CommandStatus;

final readonly class CommandExecution implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $id,
        public string $command,
        public CommandStatus $status,
        public string $output,
        public ?int $exitCode,
        public string $phase,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
