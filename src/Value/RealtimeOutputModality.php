<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum RealtimeOutputModality: string
{
    case Text = 'text';
    case Audio = 'audio';
}
