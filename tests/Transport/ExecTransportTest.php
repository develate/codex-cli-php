<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Transport\ExecTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\TestCase;

final class ExecTransportTest extends TestCase
{
    public function testBuildsStartCommandWithAllSupportedInputs(): void
    {
        $command = (new ExecTransport('/usr/bin/codex'))->command(new RunRequest(
            mode: RunMode::Start,
            threadId: null,
            prompt: 'Fix tests',
            cwd: '/project',
            model: 'gpt-test',
            sandbox: Sandbox::WorkspaceWrite,
            writableDirectories: ['/shared'],
            images: ['/tmp/a.png', '/tmp/b.png'],
        ), '/tmp/schema.json');

        self::assertSame(['/usr/bin/codex', 'exec', '--json', '--color', 'never'], array_slice($command, 0, 5));
        self::assertContains('--cd', $command);
        self::assertContains('workspace-write', $command);
        self::assertSame(2, count(array_keys($command, '--image', true)));
        self::assertSame('Fix tests', $command[array_key_last($command)]);
    }

    public function testResumeDoesNotPassUnsupportedStartOnlyFlags(): void
    {
        $command = (new ExecTransport())->command(new RunRequest(
            mode: RunMode::Resume,
            threadId: 'thread-1',
            prompt: 'Continue',
            cwd: '/project',
            model: null,
            sandbox: Sandbox::WorkspaceWrite,
            writableDirectories: ['/shared'],
        ));

        self::assertSame(['codex', 'exec', 'resume', '--json', 'thread-1', 'Continue'], $command);
    }
}
