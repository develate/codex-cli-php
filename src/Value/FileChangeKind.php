<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum FileChangeKind: string
{
    case Add = 'add';
    case Update = 'update';
    case Delete = 'delete';
    case Unknown = 'unknown';

    public static function fromCodex(mixed $value): self
    {
        return is_string($value) ? self::tryFrom($value) ?? self::Unknown : self::Unknown;
    }
}
