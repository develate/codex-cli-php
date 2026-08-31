<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

use Develate\CodexCli\Exception\CodexNotFound;
use Symfony\Component\Process\ExecutableFinder;

final class CodexExecutable
{
    public static function assertAvailable(string $binary): void
    {
        $hasPath = str_contains($binary, '/') || str_contains($binary, '\\');
        $available = $hasPath
            ? is_file($binary) && is_executable($binary)
            : (new ExecutableFinder())->find($binary) !== null;

        if (!$available) {
            throw new CodexNotFound(sprintf('Codex binary "%s" was not found or is not executable.', $binary));
        }
    }
}
