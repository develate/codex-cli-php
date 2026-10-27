<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\TurnStarted;
use Develate\CodexCli\Exception\AppServerException;
use Develate\CodexCli\Exception\JsonRpcException;
use Develate\CodexCli\Tests\Support\FakeTransport;
use Develate\CodexCli\Thread;
use Develate\CodexCli\Transport\AppServerTurnTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\TestCase;

final class SteeringTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/codex-steering-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $path) {
            unlink($path);
        }
        rmdir($this->directory);
    }

    public function testThreadSteersTheActiveTurnWithTextAndImages(): void
    {
        $transport = new AppServerTurnTransport($this->server());
        $thread = new Thread($transport, '1.0.0', '/tmp', null, Sandbox::ReadOnly);
        $accepted = null;
        $thread->onEvent(function ($event) use ($thread, &$accepted): void {
            if ($event instanceof TurnStarted) {
                $accepted = $thread->steer('Focus on tests', ['/tmp/diagram.png']);
            }
        });

        $result = $thread->run('Fix the bug');

        self::assertSame('turn-1', $accepted);
        self::assertSame(0, $result->exitCode);
        self::assertCount(1, $this->requests('turn/start'));
        self::assertSame([
            'threadId' => 'thread-1',
            'expectedTurnId' => 'turn-1',
            'input' => [
                ['type' => 'text', 'text' => 'Focus on tests'],
                ['type' => 'localImage', 'path' => '/tmp/diagram.png'],
            ],
        ], $this->requests('turn/steer')[0]['params']);

        $this->expectException(\LogicException::class);
        $thread->steer('Too late');
    }

    public function testTickCanSteerWhileTheServerEmitsNoNotifications(): void
    {
        $transport = new AppServerTurnTransport($this->server());
        $ticks = 0;
        $transport->onTick(function () use ($transport, &$ticks): void {
            if (++$ticks === 2) {
                $transport->steer('Keep scope small');
            }
        });

        $events = iterator_to_array($transport->stream($this->request()));

        self::assertSame(2, $ticks);
        self::assertInstanceOf(TurnCompleted::class, end($events));
        self::assertCount(1, $this->requests('turn/steer'));
    }

    public function testRejectedSteeringDoesNotLoseTheTurnCompletion(): void
    {
        $transport = new AppServerTurnTransport($this->server(['error' => ['code' => -32600, 'message' => 'Turn finished']]));
        $failure = null;
        $transport->onTick(function () use ($transport, &$failure): void {
            try {
                $transport->steer('Too late');
            } catch (JsonRpcException $exception) {
                $failure = $exception->getMessage();
            }
        });

        $events = iterator_to_array($transport->stream($this->request()));

        self::assertSame('Turn finished', $failure);
        self::assertInstanceOf(TurnCompleted::class, end($events));
    }

    public function testMismatchedAcknowledgementIsRejected(): void
    {
        $transport = new AppServerTurnTransport($this->server(['result' => ['turnId' => 'another-turn']]));
        $transport->onTick(fn () => $transport->steer('Keep scope small'));

        $this->expectException(AppServerException::class);
        iterator_to_array($transport->stream($this->request()));
    }

    public function testCompletionBufferedDuringTurnStartEndsTheStreamWithoutTicking(): void
    {
        $transport = new AppServerTurnTransport($this->server(finishBeforeStart: true));
        $transport->onTick(static function (): void {
            self::fail('A completed turn must not accept pending input.');
        });

        $events = iterator_to_array($transport->stream($this->request()));

        self::assertInstanceOf(TurnCompleted::class, end($events));
        self::assertSame([], $this->requests('turn/interrupt'));
    }

    public function testIdleTransportRejectsSteering(): void
    {
        $this->expectException(\LogicException::class);
        (new AppServerTurnTransport)->steer('Hello');
    }

    public function testEmptyInputIsRejectedWithoutSendingAnRpcOrEndingTheTurn(): void
    {
        $transport = new AppServerTurnTransport($this->server());
        $transport->onTick(function () use ($transport): void {
            try {
                $transport->steer('   ');
                self::fail('Empty steering must be rejected.');
            } catch (\InvalidArgumentException) {
                $transport->steer('Focus on tests');
            }
        });

        $events = iterator_to_array($transport->stream($this->request()));

        self::assertInstanceOf(TurnCompleted::class, end($events));
        self::assertCount(1, $this->requests('turn/steer'));
    }

    public function testExecThreadRejectsSteeringDuringATurn(): void
    {
        $thread = new Thread(new FakeTransport, '1.0.0', '/tmp', null, Sandbox::ReadOnly);
        $thread->onEvent(fn () => $thread->steer('Hello'));

        $this->expectException(\LogicException::class);
        $thread->run('Work');
    }

    private function request(): RunRequest
    {
        return new RunRequest(RunMode::Start, null, 'Work', '/tmp', null, Sandbox::ReadOnly, timeout: 5.0);
    }

    /** @return list<array<string, mixed>> */
    private function requests(string $method): array
    {
        $messages = array_map(static fn ($line) => json_decode($line, true), file($this->directory.'/requests.jsonl', FILE_IGNORE_NEW_LINES));

        return array_values(array_filter($messages, static fn ($message) => ($message['method'] ?? null) === $method));
    }

    /** @param array<string, mixed> $steerResponse */
    private function server(array $steerResponse = ['result' => ['turnId' => 'turn-1']], bool $finishBeforeStart = false): string
    {
        $script = strtr(<<<'PHP'
        <?php
        $response = __RESPONSE__;
        $finishBeforeStart = __FINISH__;
        $emit = static function (array $message): void {
            fwrite(STDOUT, json_encode($message)."\n");
            fflush(STDOUT);
        };
        $finish = static function () use ($emit): void {
            $emit(['method' => 'turn/completed', 'params' => [
                'threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []],
            ]]);
        };
        while (($line = fgets(STDIN)) !== false) {
            file_put_contents(__DIR__.'/requests.jsonl', $line, FILE_APPEND);
            $message = json_decode($line, true);
            if (!isset($message['id'], $message['method'])) { continue; }
            if ($message['method'] === 'turn/steer') {
                $emit(['id' => $message['id']] + $response);
                $finish();
                continue;
            }
            if ($message['method'] === 'turn/start' && $finishBeforeStart) { $finish(); }
            $result = match ($message['method']) {
                'initialize' => ['userAgent' => 'fake'],
                'thread/start' => ['thread' => ['id' => 'thread-1']],
                'turn/start' => ['turn' => ['id' => 'turn-1']],
                default => [],
            };
            $emit(['id' => $message['id'], 'result' => $result]);
        }
        PHP, ['__RESPONSE__' => var_export($steerResponse, true), '__FINISH__' => var_export($finishBeforeStart, true)]);
        file_put_contents($this->directory.'/server.php', $script);
        $binary = $this->directory.'/codex';
        file_put_contents($binary, sprintf("#!/bin/sh\nexec %s %s \"$@\"\n", escapeshellarg(PHP_BINARY), escapeshellarg($this->directory.'/server.php')));
        chmod($binary, 0700);

        return $binary;
    }
}
