<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Value\Credits;
use Develate\CodexCli\Value\Quota;
use Develate\CodexCli\Value\QuotaWindow;

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
}
