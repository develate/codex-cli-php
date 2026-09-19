<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

class ProcessFactory
{
    /**
     * @param list<string> $command
     * @param array<string, string|false> $env variables for the child; `false` removes an inherited one
     */
    public function create(array $command, ?string $cwd = null, ?float $timeout = null, array $env = []): CodexProcess
    {
        return new CodexProcess($command, $cwd, $timeout, $env);
    }
}
