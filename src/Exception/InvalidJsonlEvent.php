<?php

declare(strict_types=1);

namespace Develate\CodexCli\Exception;

final class InvalidJsonlEvent extends CodexException
{
    public function __construct(
        public readonly string $jsonlLine,
        \Throwable $previous,
    ) {
        parent::__construct('Codex emitted invalid JSONL: '.$previous->getMessage(), 0, $previous);
    }
}
