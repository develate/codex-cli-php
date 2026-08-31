<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

final readonly class ProcessResult
{
    public function __construct(public int $exitCode, public string $stderr)
    {
    }
}
