<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

final readonly class UnknownEvent implements CodexEvent
{
    /** @param array<string, mixed> $data */
    public function __construct(public string $type, public array $data)
    {
    }

    public function raw(): array
    {
        return $this->data;
    }
}
