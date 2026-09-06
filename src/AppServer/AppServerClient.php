<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Exception\AppServerException;

final class AppServerClient
{
    private bool $initialized = false;

    public function __construct(private readonly JsonRpcConnection $connection)
    {
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->connection->request('initialize', [
            'clientInfo' => [
                'name' => 'develate_codex_cli_php',
                'title' => 'Develate Codex CLI PHP',
                'version' => '0.1.0',
            ],
        ]);
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
