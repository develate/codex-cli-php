<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Value\Credits;
use Develate\CodexCli\Value\Quota;
use Develate\CodexCli\Value\QuotaWindow;
use Develate\CodexCli\Value\RateLimitResetCredit;
use Develate\CodexCli\Value\RateLimitResetCredits;

final class QuotaMapper
{
    /** @param array<string, mixed> $response */
    public function map(array $response): Quota
    {
        $snapshot = is_array($response['rateLimits'] ?? null) ? $response['rateLimits'] : [];
        $mainId = is_string($snapshot['limitId'] ?? null) ? $snapshot['limitId'] : null;
        $additional = [];
        $all = is_array($response['rateLimitsByLimitId'] ?? null) ? $response['rateLimitsByLimitId'] : [];
        foreach ($all as $id => $bucket) {
            if ($id === $mainId || !is_array($bucket)) {
                continue;
            }
            $additional[(string) $id] = $this->mapSnapshot($bucket);
        }

        return new Quota(
            primary: $this->window($snapshot['primary'] ?? null),
            secondary: $this->window($snapshot['secondary'] ?? null),
            additional: $additional,
            credits: $this->credits($snapshot['credits'] ?? null),
            resetCredits: $this->resetCredits($response['rateLimitResetCredits'] ?? null),
            raw: $response,
        );
    }

    /** @param array<string, mixed> $snapshot */
    private function mapSnapshot(array $snapshot): Quota
    {
        return new Quota(
            primary: $this->window($snapshot['primary'] ?? null),
            secondary: $this->window($snapshot['secondary'] ?? null),
            additional: [],
            credits: $this->credits($snapshot['credits'] ?? null),
            resetCredits: null,
            raw: $snapshot,
        );
    }

    private function window(mixed $value): ?QuotaWindow
    {
        if (!is_array($value)) {
            return null;
        }
        $reset = is_numeric($value['resetsAt'] ?? null)
            ? (new \DateTimeImmutable('@'.(int) $value['resetsAt']))
            : null;

        return new QuotaWindow(
            usedPercent: is_numeric($value['usedPercent'] ?? null) ? (float) $value['usedPercent'] : 0.0,
            windowMinutes: is_numeric($value['windowDurationMins'] ?? null) ? (int) $value['windowDurationMins'] : null,
            resetsAt: $reset,
        );
    }

    private function credits(mixed $value): ?Credits
    {
        if (!is_array($value)) {
            return null;
        }

        return new Credits(
            hasCredits: (bool) ($value['hasCredits'] ?? false),
            unlimited: (bool) ($value['unlimited'] ?? false),
            balance: isset($value['balance']) && is_scalar($value['balance']) ? (string) $value['balance'] : null,
            raw: $value,
        );
    }

    private function resetCredits(mixed $value): ?RateLimitResetCredits
    {
        if (! is_array($value)) {
            return null;
        }

        $credits = null;

        if (is_array($value['credits'] ?? null)) {
            $credits = [];

            foreach ($value['credits'] as $credit) {
                if (! is_array($credit)) {
                    continue;
                }

                $id = $credit['id'] ?? null;
                $status = $credit['status'] ?? null;
                $resetType = $credit['resetType'] ?? null;
                $grantedAt = $this->timestamp($credit['grantedAt'] ?? null);

                if (! is_string($id) || $id === ''
                    || ! is_string($status) || $status === ''
                    || ! is_string($resetType) || $resetType === ''
                    || $grantedAt === null) {
                    continue;
                }

                $credits[] = new RateLimitResetCredit(
                    id: $id,
                    status: $status,
                    resetType: $resetType,
                    grantedAt: $grantedAt,
                    expiresAt: $this->timestamp($credit['expiresAt'] ?? null),
                );
            }
        }

        return new RateLimitResetCredits(
            availableCount: is_numeric($value['availableCount'] ?? null) ? max(0, (int) $value['availableCount']) : 0,
            credits: $credits,
        );
    }

    private function timestamp(mixed $value): ?\DateTimeImmutable
    {
        return is_numeric($value) ? new \DateTimeImmutable('@'.(int) $value) : null;
    }
}
