<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum Sandbox: string
{
    case ReadOnly = 'read-only';
    case WorkspaceWrite = 'workspace-write';
    case FullAccess = 'danger-full-access';
}
