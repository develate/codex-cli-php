<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

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

    /** @param callable(Notification): void $listener */
    public function onNotification(callable $listener): void
    {
        $this->connection->onNotification($listener);
    }
}
