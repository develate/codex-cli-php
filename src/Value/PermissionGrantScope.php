<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

/** How long a granted additional permission remains available. */
enum PermissionGrantScope: string
{
    case Turn = 'turn';

    case Session = 'session';
}
