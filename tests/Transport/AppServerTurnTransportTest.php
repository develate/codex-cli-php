<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\ThreadStarted;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\TurnFailed;
use Develate\CodexCli\Exception\UnsupportedApproval;
use Develate\CodexCli\Transport\ApprovalRequest;
use Develate\CodexCli\Transport\AppServerTurnTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Value\ApprovalDecision;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\TestCase;

/**
 * The interactive transport, against a fake app server that speaks the real
 * protocol both ways.
 */
final class AppServerTurnTransportTest extends TestCase
{
    /** @var list<string> */
    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function testStreamsATurnAsTheSameEventsTheExecTransportProduces(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/started', ['item' => ['type' => 'agentMessage', 'id' => 'i1', 'text' => 'Working']]),
            $this->notification('item/completed', ['item' => [
                'type' => 'commandExecution', 'id' => 'c1', 'command' => 'ls',
                'status' => 'completed', 'aggregatedOutput' => 'README.md', 'exitCode' => 0,
            ]]),
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => [
                'id' => 'turn-1', 'status' => 'completed', 'items' => [],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 3],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());

        self::assertInstanceOf(ThreadStarted::class, $events[0]);
        self::assertSame('thread-1', $events[0]->threadId);
        self::assertInstanceOf(AgentMessage::class, $events[1]);
        self::assertSame('Working', $events[1]->text);
        self::assertInstanceOf(CommandExecution::class, $events[2]);
        self::assertSame('ls', $events[2]->command);
        self::assertSame('README.md', $events[2]->output);
        self::assertInstanceOf(TurnCompleted::class, $events[3]);
    }

    public function testACommandApprovalIsAnsweredWhileTheAgentWaits(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/commandExecution/requestApproval', [
                'itemId' => 'c1',
                'command' => 'rm -rf build',
                'cwd' => '/tmp/project',
            ]),
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $asked = [];
        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(function (ApprovalRequest $request) use (&$asked): ApprovalDecision {
            $asked[] = $request;

            return ApprovalDecision::Accept;
        });

        $this->collect($transport, $this->request());

        self::assertCount(1, $asked);
        self::assertSame(ApprovalRequest::KIND_COMMAND, $asked[0]->kind);
        self::assertSame('rm -rf build', $asked[0]->subject, 'The command must reach the user in full.');
        self::assertSame('accept', $this->decisions($binary)[0]);
    }

    public function testADeniedApprovalIsSentBackAsADecline(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/fileChange/requestApproval', ['itemId' => 'f1', 'reason' => 'write outside workspace']),
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(fn (ApprovalRequest $request): ApprovalDecision => ApprovalDecision::Decline);

        $this->collect($transport, $this->request());

        self::assertSame('decline', $this->decisions($binary)[0]);
    }

    public function testAnApprovalNobodyCanAnswerFailsTheTurnRatherThanGrantingAccess(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/commandExecution/requestApproval', ['itemId' => 'c1', 'command' => 'curl evil.example']),
        ]);

        // No handler registered: there is nobody to ask.
        $this->expectException(UnsupportedApproval::class);

        $this->collect(new AppServerTurnTransport($binary), $this->request());
    }

    public function testAnApprovalKindThisClientDoesNotKnowFailsVisibly(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/somethingNew/requestApproval', ['itemId' => 'x1']),
        ]);

        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(fn (ApprovalRequest $request): ApprovalDecision => ApprovalDecision::Accept);

        $this->expectException(UnsupportedApproval::class);

        $this->collect($transport, $this->request());
    }

    public function testAFailedTurnIsReportedAsAFailure(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => [
                'id' => 'turn-1', 'status' => 'failed', 'items' => [],
                'error' => ['message' => 'The model refused.'],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());
        $failure = end($events);

        self::assertInstanceOf(TurnFailed::class, $failure);
        self::assertSame('The model refused.', $failure->message);
    }

    public function testResumingSendsTheThreadIdRatherThanStartingANewThread(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $this->collect(new AppServerTurnTransport($binary), $this->request(mode: RunMode::Resume, threadId: 'existing-thread'));

        $methods = array_column($this->requests($binary), 'method');

        self::assertContains('thread/resume', $methods);
        self::assertNotContains('thread/start', $methods);
    }

    public function testAbandoningTheStreamInterruptsTheTurn(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/started', ['item' => ['type' => 'agentMessage', 'id' => 'i1', 'text' => 'Thinking']]),
            $this->notification('turn/completed', ['threadId' => 't1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $stream = (new AppServerTurnTransport($binary))->stream($this->request());

        foreach ($stream as $event) {
            if ($event instanceof AgentMessage) {
                break;
            }
        }

        unset($stream);

        self::assertContains('turn/interrupt', array_column($this->requests($binary), 'method'));
    }

    private function request(RunMode $mode = RunMode::Start, ?string $threadId = null): RunRequest
    {
        return new RunRequest(
            mode: $mode,
            threadId: $threadId,
            prompt: 'Do the thing',
            cwd: sys_get_temp_dir(),
            model: 'gpt-5-codex',
            sandbox: Sandbox::WorkspaceWrite,
            timeout: 10.0,
        );
    }

    /**
     * @return list<\Develate\CodexCli\Event\CodexEvent>
     */
    private function collect(AppServerTurnTransport $transport, RunRequest $request): array
    {
        $events = [];

        foreach ($transport->stream($request) as $event) {
            $events[] = $event;
        }

        return $events;
    }

    /** @return array<string, mixed> */
    private function notification(string $method, array $params): array
    {
        return ['method' => $method, 'params' => $params];
    }

    /** @return array<string, mixed> */
    private function serverRequest(string $method, array $params): array
    {
        return ['method' => $method, 'params' => $params, 'expectsResponse' => true];
    }

    /** @return list<array<string, mixed>> */
    private function requests(string $binary): array
    {
        $log = dirname($binary).DIRECTORY_SEPARATOR.'requests.jsonl';

        if (! is_file($log)) {
            return [];
        }

        return array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            array_filter(file($log, FILE_IGNORE_NEW_LINES)),
        );
    }

    /** @return list<string> */
    private function decisions(string $binary): array
    {
        $decisions = [];

        foreach ($this->requests($binary) as $message) {
            if (isset($message['result']['decision'])) {
                $decisions[] = $message['result']['decision'];
            }
        }

        return $decisions;
    }

    /**
     * A fake `codex app-server` that answers the calls this transport makes and
     * then plays a scripted sequence of notifications and server requests.
     *
     * @param  list<array<string, mixed>>  $script
     */
    private function fakeAppServer(array $script): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'codex-turn-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $server = $directory.DIRECTORY_SEPARATOR.'app-server.php';
        $binary = $directory.DIRECTORY_SEPARATOR.'codex';
        $log = $directory.DIRECTORY_SEPARATOR.'requests.jsonl';

        $body = strtr(<<<'PHP'
        <?php
        $script = json_decode(__SCRIPT__, true);
        $log = __LOG__;
        $nextId = 9000;
        $emit = static function (array $message) use ($log): void {
            fwrite(STDOUT, json_encode($message)."\n");
            fflush(STDOUT);
        };
        $play = static function () use (&$script, &$nextId, $emit): void {
            while ($script !== []) {
                $step = array_shift($script);
                if (($step['expectsResponse'] ?? false) === true) {
                    $emit(['id' => $nextId++, 'method' => $step['method'], 'params' => $step['params']]);
                    return;
                }
                $emit(['method' => $step['method'], 'params' => $step['params']]);
            }
        };
        while (($line = fgets(STDIN)) !== false) {
            $message = json_decode(trim($line), true);
            if (! is_array($message)) {
                continue;
            }
            file_put_contents($log, json_encode($message)."\n", FILE_APPEND);
            if (! isset($message['method'])) {
                // A response to one of our own server requests: carry on.
                $play();
                continue;
            }
            if (! isset($message['id'])) {
                continue;
            }
            $result = match ($message['method']) {
                'initialize' => ['userAgent' => 'fake'],
                'thread/start', 'thread/resume' => ['thread' => ['id' => 'thread-1']],
                'turn/start' => ['turn' => ['id' => 'turn-1', 'status' => 'inProgress', 'items' => []]],
                default => [],
            };
            $emit(['id' => $message['id'], 'result' => $result]);
            if ($message['method'] === 'turn/start') {
                $play();
            }
        }
        PHP, ['__SCRIPT__' => var_export(json_encode($script, JSON_THROW_ON_ERROR), true), '__LOG__' => var_export($log, true)]);

        file_put_contents($server, $body);
        file_put_contents($binary, sprintf("#!/bin/sh\nexec %s %s \"\$@\"\n", escapeshellarg(PHP_BINARY), escapeshellarg($server)));
        chmod($binary, 0700);

        return $binary;
    }
}
