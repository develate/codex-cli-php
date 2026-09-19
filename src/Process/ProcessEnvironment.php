<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

/**
 * Resolves a per-process environment for spawners that have no convention of
 * their own for removing an inherited variable.
 *
 * Symfony Process merges the given variables into the parent's environment and
 * drops the ones set to `false`. `proc_open()` takes either a complete
 * environment or nothing at all, so the same merge has to be done by hand
 * before the app server starts.
 */
final class ProcessEnvironment
{
    /**
     * @param  array<string, string|false>  $env
     * @return array<string, string>|null null when the child should simply inherit
     */
    public static function resolve(array $env): ?array
    {
        if ($env === []) {
            return null;
        }

        $resolved = [];

        foreach ($env + $_ENV + getenv() as $variable => $value) {
            if ($value === false) {
                continue;
            }

            $resolved[(string) $variable] = (string) $value;
        }

        return $resolved;
    }
}
