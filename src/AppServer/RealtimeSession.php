<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Value\RealtimeAudioChunk;
use Develate\CodexCli\Value\RealtimeStartRequest;
use Develate\CodexCli\Value\RealtimeTextRole;

final class RealtimeSession
{
    private bool $closed = false;

    /** @var array<string, true> */
    private array $loadedThreads = [];

    public function __construct(private readonly AppServerClient $client, private readonly JsonRpcConnection $connection)
    {
    }

    public function start(RealtimeStartRequest $request): void
    {
        $this->assertOpen();
        if (!isset($this->loadedThreads[$request->threadId])) {
            $this->client->threadResume($request->threadId);
            $this->loadedThreads[$request->threadId] = true;
        }
        $this->client->realtimeStart($request);
    }

    public function appendAudio(RealtimeAudioChunk $audio, string $threadId): void
    {
        $this->assertOpen();
        $this->client->realtimeAppendAudio($threadId, $audio);
    }

    public function appendText(string $threadId, string $text, RealtimeTextRole $role = RealtimeTextRole::User): void
    {
        $this->assertOpen();
        $this->client->realtimeAppendText($threadId, $text, $role);
    }

    public function appendSpeech(string $threadId, string $text): void
    {
        $this->assertOpen();
        $this->client->realtimeAppendSpeech($threadId, $text);
    }

    public function stop(string $threadId): void
    {
        $this->assertOpen();
        $this->client->realtimeStop($threadId);
    }

    /** @param callable(RealtimeNotification): void $listener */
    public function onNotification(callable $listener): void
    {
        $this->client->onNotification(static function (Notification $notification) use ($listener): void {
            if (str_starts_with($notification->method, 'thread/realtime/')) {
                $listener(RealtimeNotification::from($notification));
            }
        });
    }

    public function pump(float $timeout): bool
    {
        $this->assertOpen();
        return $this->client->pump($timeout);
    }

    /** @param callable(ServerRequest): array<string, mixed> $handler */
    public function onServerRequest(callable $handler): void
    {
        $this->client->onServerRequest($handler);
    }

    /** @return list<RealtimeNotification> */
    public function drain(): array
    {
        return array_values(array_map(RealtimeNotification::from(...), array_filter(
            $this->client->drain(),
            static fn (Notification $n): bool => str_starts_with($n->method, 'thread/realtime/')
        )));
    }

    public function close(): void
    {
        $this->closed = true;
        $this->connection->close();
    }

    private function assertOpen(): void
    {
        if ($this->closed) {
            throw new \LogicException('The realtime connection is closed.');
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
