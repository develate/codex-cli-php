<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Process;

use Develate\CodexCli\Exception\CodexNotFound;
use Develate\CodexCli\Process\CodexExecutable;
use PHPUnit\Framework\TestCase;

final class CodexExecutableTest extends TestCase
{
    public function testMissingExecutableHasASpecificFailure(): void
    {
        $this->expectException(CodexNotFound::class);
        CodexExecutable::assertAvailable('/definitely/missing/codex');
    }
}
