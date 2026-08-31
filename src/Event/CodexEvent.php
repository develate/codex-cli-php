<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

interface CodexEvent
{
    /** @return array<string, mixed> */
    public function raw(): array;
}
