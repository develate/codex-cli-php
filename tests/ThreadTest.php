<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests;

use Develate\CodexCli\Thread;
use Develate\CodexCli\Tests\Support\FakeTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\TestCase;

final class ThreadTest extends TestCase
{
    public function testFirstRunStartsAndFollowingRunResumes(): void
    {
        $transport = new FakeTransport();
        $thread = $this->thread($transport);

        $first = $thread->run('first');
        $second = $thread->run('second');

        self::assertSame('thread-one', $thread->id());
        self::assertSame('answer: first', $first->response);
        self::assertSame(10, $second->usage->inputTokens);
        self::assertSame(RunMode::Start, $transport->requests[0]->mode);
        self::assertSame(RunMode::Resume, $transport->requests[1]->mode);
        self::assertSame('thread-one', $transport->requests[1]->threadId);
    }

    public function testForkCreatesAChildOnItsFirstRun(): void
    {
        $transport = new FakeTransport();
        $thread = $this->thread($transport);
        $thread->run('parent');

        $branch = $thread->fork();
        $branch->run('child');

        self::assertSame(RunMode::Fork, $transport->requests[1]->mode);
        self::assertSame('thread-one', $transport->requests[1]->threadId);
        self::assertSame('thread-child', $branch->id());
        self::assertSame('thread-one', $thread->id());
    }

    private function thread(FakeTransport $transport): Thread
    {
        return new Thread($transport, '0.150.1', '/project', null, Sandbox::ReadOnly);
    }
}
