<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum RealtimeTextRole: string
{
    case User = 'user';
    case Developer = 'developer';
    case Assistant = 'assistant';
}
