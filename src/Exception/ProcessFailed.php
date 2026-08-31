<?php

declare(strict_types=1);

namespace Develate\CodexCli\Exception;

final class ProcessFailed extends CodexException
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stderr = '',
    ) {
        parent::__construct(sprintf(
            'Codex exited with code %d%s',
            $exitCode,
            $stderr === '' ? '.' : ': '.trim($stderr),
        ), $exitCode);
    }
}
