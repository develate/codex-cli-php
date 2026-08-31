<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum CommandStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';
    case Declined = 'declined';
    case Unknown = 'unknown';

    public static function fromCodex(mixed $value): self
    {
        return is_string($value) ? self::tryFrom($value) ?? self::Unknown : self::Unknown;
    }
}
