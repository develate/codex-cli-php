<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

use Develate\CodexCli\Value\Usage;

final readonly class TurnCompleted implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(public Usage $usage, private array $raw)
    {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
