<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

class ProcessFactory
{
    /** @param list<string> $command */
    public function create(array $command, ?string $cwd = null, ?float $timeout = null): CodexProcess
    {
        return new CodexProcess($command, $cwd, $timeout);
    }
}
