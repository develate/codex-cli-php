<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Exception\AppServerException;
use Develate\CodexCli\Value\RealtimeAudioChunk;
use Develate\CodexCli\Value\RealtimeStartRequest;
use Develate\CodexCli\Value\RealtimeTextRole;

final class AppServerClient
{
    private bool $initialized = false;

    public function __construct(private readonly JsonRpcConnection $connection, private readonly bool $experimentalApi = false)
    {
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $params = [
            'clientInfo' => [
                'name' => 'develate_codex_cli_php',
                'title' => 'Develate Codex CLI PHP',
                'version' => '0.1.0',
            ],
        ];
        if ($this->experimentalApi) {
            $params['capabilities'] = ['experimentalApi' => true];
        }
        $this->connection->request('initialize', $params);
        $this->connection->notify('initialized');
        $this->initialized = true;
    }

    /** @return array<string, mixed> */
    public function rateLimits(): array
    {
        $this->initialize();
        $result = $this->connection->request('account/rateLimits/read');

        return is_array($result) ? $result : [];
    }

    /** @return array<string, mixed> */
    public function account(bool $refreshToken = false): array
    {
        $this->initialize();
        $result = $this->connection->request('account/read', ['refreshToken' => $refreshToken]);

        return is_array($result) ? $result : [];
    }

    /** @return array<string, mixed> */
    public function consumeRateLimitResetCredit(string $idempotencyKey, ?string $creditId = null): array
    {
        if (trim($idempotencyKey) === '') {
            throw new \InvalidArgumentException('The idempotency key must not be empty.');
        }

        if ($creditId !== null && trim($creditId) === '') {
            throw new \InvalidArgumentException('The credit ID must not be empty when provided.');
        }

        $this->initialize();
        $result = $this->connection->request('account/rateLimitResetCredit/consume', array_filter([
            'idempotencyKey' => $idempotencyKey,
            'creditId' => $creditId,
        ], static fn (mixed $value): bool => $value !== null));

        return is_array($result) ? $result : [];
    }

    /**
     * Opens a fresh thread and returns its ID.
     *
     * @param  array<string, mixed>  $params
     */
    public function threadStart(array $params): string
    {
        $this->initialize();
        $result = $this->connection->request('thread/start', $params);
        $thread = is_array($result) ? ($result['thread'] ?? null) : null;
        $id = is_array($thread) ? ($thread['id'] ?? null) : null;

        if (!is_string($id) || $id === '') {
            throw new AppServerException('The Codex app server started a thread without naming it.');
        }

        return $id;
    }

    /**
     * Rejoins an existing thread.
     *
     * @param  array<string, mixed>  $params
     */
    public function threadResume(string $threadId, array $params = []): string
    {
        $this->initialize();
        $result = $this->connection->request('thread/resume', array_merge([
            'threadId' => $threadId,

            // The history is already on disk and the caller does not read it
            // back through this call, so hydrating every turn only makes the
            // response large.
            'excludeTurns' => true,
        ], $params));

        $thread = is_array($result) ? ($result['thread'] ?? null) : null;
        $id = is_array($thread) ? ($thread['id'] ?? null) : null;

        return is_string($id) && $id !== '' ? $id : $threadId;
    }

    /**
     * Branches an existing thread's stored history and returns the new ID.
     *
     * @param array<string, mixed> $params
     */
    public function threadFork(string $threadId, array $params = []): string
    {
        if (trim($threadId) === '') {
            throw new \InvalidArgumentException('The parent thread ID must not be empty.');
        }

        $this->initialize();
        $result = $this->connection->request('thread/fork', array_merge(
            ['excludeTurns' => true],
            $params,
            ['threadId' => $threadId],
        ));
        $thread = is_array($result) ? ($result['thread'] ?? null) : null;
        $id = is_array($thread) ? ($thread['id'] ?? null) : null;

        if (!is_string($id) || $id === '' || $id === $threadId) {
            throw new AppServerException('The Codex app server forked a thread without a new thread ID.');
        }

        return $id;
    }

    /**
     * Starts a turn and returns its ID. The work itself arrives as
     * notifications afterwards.
     *
     * @param  array<string, mixed>  $params
     */
    public function turnStart(array $params): string
    {
        $result = $this->connection->request('turn/start', $params);
        $turn = is_array($result) ? ($result['turn'] ?? null) : null;
        $id = is_array($turn) ? ($turn['id'] ?? null) : null;

        if (!is_string($id) || $id === '') {
            throw new AppServerException('The Codex app server started a turn without naming it.');
        }

        return $id;
    }

    /**
     * Adds input to the named active turn without starting another one.
     *
     * @param list<array<string, mixed>> $input
     */
    public function turnSteer(string $threadId, string $expectedTurnId, array $input): string
    {
        $result = $this->connection->request('turn/steer', [
            'threadId' => $threadId,
            'expectedTurnId' => $expectedTurnId,
            'input' => $input,
        ]);
        $id = is_array($result) ? ($result['turnId'] ?? null) : null;

        if (!is_string($id) || $id !== $expectedTurnId) {
            throw new AppServerException('The Codex app server did not acknowledge steering the expected turn.');
        }

        return $id;
    }

    /**
     * Asks the agent to stop.
     *
     * Sent on a best-effort basis: the turn may have finished between the
     * decision to cancel and this call, and a server that no longer knows the
     * turn is not an error worth surfacing.
     */
    public function turnInterrupt(string $threadId, string $turnId): void
    {
        try {
            $this->connection->request('turn/interrupt', [
                'threadId' => $threadId,
                'turnId' => $turnId,
            ], timeout: 5.0);
        } catch (\Throwable) {
            // Nothing left to interrupt.
        }
    }

    public function realtimeStart(RealtimeStartRequest $request): void
    {
        $this->initialize();
        $this->connection->request('thread/realtime/start', $request->toArray());
    }

    public function realtimeAppendAudio(string $threadId, RealtimeAudioChunk $audio): void
    {
        $this->connection->request('thread/realtime/appendAudio', ['threadId' => $threadId, 'audio' => $audio->toArray()]);
    }

    public function realtimeAppendText(string $threadId, string $text, RealtimeTextRole $role = RealtimeTextRole::User): void
    {
        $this->connection->request('thread/realtime/appendText', ['threadId' => $threadId, 'text' => $text, 'role' => $role->value]);
    }

    public function realtimeAppendSpeech(string $threadId, string $text): void
    {
        $this->connection->request('thread/realtime/appendSpeech', ['threadId' => $threadId, 'text' => $text]);
    }

    public function realtimeStop(string $threadId): void
    {
        $this->connection->request('thread/realtime/stop', ['threadId' => $threadId]);
    }

    /** @param callable(Notification): void $listener */
    public function onNotification(callable $listener): void
    {
        $this->connection->onNotification($listener);
    }

    /** @param callable(ServerRequest): array<string, mixed> $handler */
    public function onServerRequest(callable $handler): void
    {
        $this->connection->onServerRequest($handler);
    }

    public function pump(float $timeout): bool
    {
        return $this->connection->pump($timeout);
    }

    /** @return list<Notification> */
    public function drain(): array
    {
        return $this->connection->drain();
    }
}
