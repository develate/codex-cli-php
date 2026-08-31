<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Process\ProcessResult;

interface Transport
{
    /** @return \Generator<int, CodexEvent, void, ProcessResult> */
    public function stream(RunRequest $request): \Generator;
}
