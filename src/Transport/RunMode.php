<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

enum RunMode
{
    case Start;
    case Resume;
    case Fork;
}
