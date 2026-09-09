<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\AppServer\AppServerClient;
use Develate\CodexCli\AppServer\JsonRpcConnection;
use Develate\CodexCli\AppServer\QuotaMapper;
use Develate\CodexCli\AppServer\RealtimeSession;
use Develate\CodexCli\Value\Account;
use Develate\CodexCli\Value\ConsumeRateLimitResetCreditOutcome;
use Develate\CodexCli\Value\ConsumeRateLimitResetCreditResult;
use Develate\CodexCli\Value\Quota;

final class AppServerTransport
{
    public function __construct(
        private readonly string $binary = 'codex',
        private readonly float $timeout = 10.0,
        private readonly QuotaMapper $quotaMapper = new QuotaMapper(),
    ) {
    }

    public function quota(): Quota
    {
        return $this->withClient(fn (AppServerClient $client): Quota => $this->quotaMapper->map($client->rateLimits()));
    }

    public function account(): Account
    {
        return $this->withClient(function (AppServerClient $client): Account {
            $response = $client->account();
            $account = is_array($response['account'] ?? null) ? $response['account'] : [];

            return new Account(
                type: is_string($account['type'] ?? null) ? $account['type'] : null,
                email: is_string($account['email'] ?? null) ? $account['email'] : null,
                planType: is_string($account['planType'] ?? null) ? $account['planType'] : null,
                requiresOpenaiAuth: (bool) ($response['requiresOpenaiAuth'] ?? false),
                raw: $response,
            );
        });
    }

    public function consumeRateLimitResetCredit(string $idempotencyKey, ?string $creditId = null): ConsumeRateLimitResetCreditResult
    {
        return $this->withClient(function (AppServerClient $client) use ($idempotencyKey, $creditId): ConsumeRateLimitResetCreditResult {
            $response = $client->consumeRateLimitResetCredit($idempotencyKey, $creditId);
            $outcome = $response['outcome'] ?? null;

            if (! is_string($outcome) || ($typedOutcome = ConsumeRateLimitResetCreditOutcome::tryFrom($outcome)) === null) {
                throw new \RuntimeException('Codex returned an unknown rate-limit reset-credit outcome.');
            }

            return new ConsumeRateLimitResetCreditResult($typedOutcome);
        });
    }

    /** Opens a persistent experimental realtime app-server session. */
    public function realtime(): RealtimeSession
    {
        $connection = new JsonRpcConnection($this->binary, $this->timeout);
        $client = new AppServerClient($connection, experimentalApi: true);
        try {
            $client->initialize();
        } catch (\Throwable $exception) {
            $connection->close();
            throw $exception;
        }

        return new RealtimeSession($client, $connection);
    }

    /** @template T @param callable(AppServerClient): T $callback @return T */
    private function withClient(callable $callback): mixed
    {
        $connection = new JsonRpcConnection($this->binary, $this->timeout);
        try {
            return $callback(new AppServerClient($connection));
        } finally {
            $connection->close();
        }
    }
}
