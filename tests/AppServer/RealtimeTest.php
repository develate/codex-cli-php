<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\AppServer;

use Develate\CodexCli\AppServer\RealtimeNotification;
use Develate\CodexCli\Transport\AppServerTransport;
use Develate\CodexCli\Value\RealtimeAudioChunk;
use Develate\CodexCli\Value\RealtimeStartRequest;
use Develate\CodexCli\Value\RealtimeTextRole;
use Develate\CodexCli\Value\RealtimeTransport;
use PHPUnit\Framework\TestCase;

final class RealtimeTest extends TestCase
{
    /** @var list<string> */
    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_dir($path)) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    public function testStartsWebRtcAndSendsTypedRealtimeInputs(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-realtime-');
        self::assertIsString($requests);
        $binary = $this->server([
            ['method' => 'thread/status/changed', 'params' => ['threadId' => 'thread-1']],
            ['method' => 'thread/realtime/sdp', 'params' => ['threadId' => 'thread-1', 'sdp' => 'answer']],
            ['method' => 'thread/realtime/transcript/delta', 'params' => ['threadId' => 'thread-1', 'role' => 'user', 'delta' => 'hello']],
            ['method' => 'thread/realtime/outputAudio/delta', 'params' => ['threadId' => 'thread-1', 'audio' => [
                'data' => 'AQI=', 'sampleRate' => 24000, 'numChannels' => 1, 'samplesPerChannel' => 2,
            ]]],
        ], $requests);
        $session = (new AppServerTransport($binary))->realtime();

        $session->start(new RealtimeStartRequest(
            threadId: 'thread-1',
            transport: RealtimeTransport::webrtc('offer'),
            prompt: null,
        ));
        $session->appendAudio(new RealtimeAudioChunk('AQI=', 24000, 1, 2), 'thread-1');
        $session->appendText('thread-1', 'hello', RealtimeTextRole::Developer);
        $session->appendSpeech('thread-1', 'hello aloud');
        $session->stop('thread-1');

        $notifications = $session->drain();
        self::assertInstanceOf(RealtimeNotification::class, $notifications[0]);
        self::assertSame('answer', $notifications[0]->sdp);
        self::assertSame('hello', $notifications[1]->delta);
        self::assertSame(24000, $notifications[2]->audio?->sampleRate);

        $messages = array_map('json_decode', array_filter(file($requests, FILE_IGNORE_NEW_LINES)));
        self::assertSame([
            'initialize', 'initialized', 'thread/resume', 'thread/realtime/start', 'thread/realtime/appendAudio',
            'thread/realtime/appendText', 'thread/realtime/appendSpeech', 'thread/realtime/stop',
        ], array_map(static fn (\stdClass $message): string => $message->method, $messages));
        self::assertTrue($messages[0]->params->capabilities->experimentalApi);
        self::assertSame('thread-1', $messages[2]->params->threadId);
        $start = $messages[3];
        self::assertSame(['type' => 'webrtc', 'sdp' => 'offer'], (array) $start->params->transport);
        self::assertSame('developer', $messages[5]->params->role);
        self::assertSame(['threadId' => 'thread-1', 'audio' => [
            'data' => 'AQI=', 'sampleRate' => 24000, 'numChannels' => 1, 'samplesPerChannel' => 2,
        ]], json_decode(json_encode($messages[4]->params), true));
        self::assertSame('hello aloud', $messages[6]->params->text);
        self::assertSame('thread-1', $messages[7]->params->threadId);
        $session->close();
    }

    public function testClosedSessionsCannotSilentlyReconnect(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-realtime-');
        $session = (new AppServerTransport($this->server([], $requests)))->realtime();
        $session->close();
        $this->expectException(\LogicException::class);
        $session->appendText('thread-1', 'hello');
    }

    public function testServerErrorsPropagate(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-realtime-');
        $session = (new AppServerTransport($this->server([], $requests)))->realtime();
        try {
            $session->appendSpeech('thread-1', 'fail');
            self::fail('The server error must reach the caller.');
        } catch (\Develate\CodexCli\Exception\JsonRpcException $error) {
            self::assertSame('Unsupported speech', $error->getMessage());
        } finally {
            $session->close();
        }
    }

    public function testOptionalStartFieldsAndTransportVariants(): void
    {
        $default = (new RealtimeStartRequest('t'))->toArray();
        self::assertSame(['threadId' => 't', 'outputModality' => 'audio'], $default);
        $explicit = (new RealtimeStartRequest('t', prompt: null, includeStartupContext: false, omitPrompt: false))->toArray();
        self::assertArrayHasKey('prompt', $explicit);
        self::assertNull($explicit['prompt']);
        self::assertFalse($explicit['includeStartupContext']);
        self::assertSame('', (new RealtimeStartRequest('t', prompt: ''))->toArray()['prompt']);
        self::assertSame(['type' => 'websocket'], RealtimeTransport::websocket()->toArray());
        self::assertSame(['type' => 'existingCall', 'callId' => 'call-1'], RealtimeTransport::existingCall('call-1')->toArray());
    }

    public function testPumpRetainsFragmentAcrossTimeout(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-realtime-');
        $session = (new AppServerTransport($this->server([], $requests)))->realtime();
        $session->appendText('thread-1', 'fragment');
        self::assertFalse($session->pump(0.02));
        self::assertTrue($session->pump(1.0));
        self::assertSame('fragmented', $session->drain()[0]->text);
        $session->close();
    }

    public function testNotificationsPreserveProtocolFieldsAndUnknownEvents(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-realtime-');
        $script = [
            ['method' => 'thread/realtime/started', 'params' => ['threadId' => 'thread-1', 'realtimeSessionId' => 'r1', 'version' => 'v3']],
            ['method' => 'thread/realtime/transcript/done', 'params' => ['threadId' => 'thread-1', 'role' => 'assistant', 'text' => 'done']],
            ['method' => 'thread/realtime/itemAdded', 'params' => ['threadId' => 'thread-1', 'item' => ['type' => 'handoff_request']]],
            ['method' => 'thread/realtime/item/transcript/delta', 'params' => ['threadId' => 'thread-1', 'itemId' => 'i1', 'delta' => 'hi']],
            ['method' => 'thread/realtime/error', 'params' => ['threadId' => 'thread-1', 'message' => 'backend failed']],
            ['method' => 'thread/realtime/closed', 'params' => ['threadId' => 'thread-1', 'reason' => null]],
            ['method' => 'thread/realtime/future', 'params' => ['threadId' => 'thread-1', 'extra' => 42]],
        ];
        $session = (new AppServerTransport($this->server($script, $requests)))->realtime();
        $heard = [];
        $session->onNotification(static function (RealtimeNotification $notification) use (&$heard): void {
            $heard[] = $notification;
        });
        $session->start(new RealtimeStartRequest('thread-1'));
        $events = $session->drain();
        self::assertCount(7, $heard);
        self::assertSame('v3', $events[0]->version);
        self::assertSame('r1', $events[0]->realtimeSessionId);
        self::assertSame('done', $events[1]->text);
        self::assertSame(['type' => 'handoff_request'], $events[2]->item);
        self::assertSame('i1', $events[3]->itemId);
        self::assertSame('backend failed', $events[4]->message);
        self::assertNull($events[5]->reason);
        self::assertSame($script[6], $events[6]->raw);
        self::assertSame([], $session->drain());
        $session->close();
    }

    /** @param list<array<string, mixed>> $notifications */
    private function server(array $notifications, string $requests): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'codex-realtime-'.bin2hex(random_bytes(5));
        mkdir($directory);
        $path = $directory.DIRECTORY_SEPARATOR.'server.php';
        $body = strtr(<<<'PHP'
        <?php
        $notifications = json_decode(__NOTIFICATIONS__, true);
        $experimental = false;
        $loaded = false;
        while (($line = fgets(STDIN)) !== false) {
            $request = json_decode(trim($line), true);
            if (! is_array($request)) continue;
            file_put_contents(__REQUESTS__, json_encode($request)."\n", FILE_APPEND);
            if (($request['method'] ?? '') === 'initialize') {
                $experimental = $request['params']['capabilities']['experimentalApi'] ?? false;
            }
            if (($request['method'] ?? '') === 'thread/resume') $loaded = true;
            if (($request['method'] ?? '') === 'thread/realtime/start') {
                if (!$experimental || !$loaded) {
                    echo json_encode(['id' => $request['id'], 'error' => ['code' => -32600, 'message' => 'Missing experimental opt-in or loaded thread']])."\n";
                    fflush(STDOUT);
                    continue;
                }
                foreach ($notifications as $notification) echo json_encode($notification)."\n";
            }
            if (($request['params']['text'] ?? '') === 'fail') {
                echo json_encode(['id' => $request['id'], 'error' => ['code' => -32601, 'message' => 'Unsupported speech']])."\n";
                fflush(STDOUT);
                continue;
            }
            if (isset($request['id'])) {
                echo json_encode(['id' => $request['id'], 'result' => []])."\n";
                fflush(STDOUT);
            }
            if (($request['params']['text'] ?? '') === 'fragment') {
                fwrite(STDOUT, '{"method":"thread/realtime/transcript/done","params":');
                fflush(STDOUT);
                usleep(200000);
                fwrite(STDOUT, '{"threadId":"thread-1","text":"fragmented","role":"user"}}'."\n");
                fflush(STDOUT);
            }
        }
        PHP, ['__NOTIFICATIONS__' => var_export(json_encode($notifications, JSON_THROW_ON_ERROR), true), '__REQUESTS__' => var_export($requests, true)]);
        file_put_contents($path, $body);
        $binary = $directory.DIRECTORY_SEPARATOR.'codex';
        file_put_contents($binary, sprintf("#!/bin/sh\nexec %s %s \"\$@\"\n", escapeshellarg(PHP_BINARY), escapeshellarg($path)));
        chmod($binary, 0700);
        $this->cleanup[] = $path;
        $this->cleanup[] = $binary;
        $this->cleanup[] = $requests;
        $this->cleanup[] = $directory;

        return $binary;
    }
}
