<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\AppServer;

use Develate\CodexCli\AppServer\QuotaMapper;
use PHPUnit\Framework\TestCase;

final class QuotaMapperTest extends TestCase
{
    public function testMapsGenericWindowsCreditsAndAdditionalBuckets(): void
    {
        $quota = (new QuotaMapper())->map([
            'rateLimits' => [
                'limitId' => 'codex',
                'primary' => ['usedPercent' => 25, 'windowDurationMins' => 15, 'resetsAt' => 1_730_947_200],
                'secondary' => null,
                'credits' => ['hasCredits' => true, 'unlimited' => false, 'balance' => '12.50'],
            ],
            'rateLimitsByLimitId' => [
                'codex' => ['primary' => ['usedPercent' => 25]],
                'other' => ['primary' => ['usedPercent' => 42, 'windowDurationMins' => 60]],
            ],
            'rateLimitResetCredits' => [
                'availableCount' => 2,
                'credits' => [[
                    'id' => 'RateLimitResetCredit_1',
                    'resetType' => 'codexRateLimits',
                    'status' => 'available',
                    'grantedAt' => 1_781_654_400,
                    'expiresAt' => 1_784_246_400,
                ]],
            ],
            'futureField' => ['preserved' => true],
        ]);

        self::assertSame(75.0, $quota->primary?->remainingPercent());
        self::assertSame(15, $quota->primary?->windowMinutes);
        self::assertTrue($quota->credits?->hasCredits);
        self::assertSame('12.50', $quota->credits?->balance);
        self::assertSame(58.0, $quota->additional['other']->primary?->remainingPercent());
        self::assertSame(2, $quota->resetCredits?->availableCount);
        self::assertSame('RateLimitResetCredit_1', $quota->resetCredits?->credits[0]->id);
        self::assertSame('2026-07-17T00:00:00+00:00', $quota->resetCredits?->credits[0]->expiresAt?->format(DATE_ATOM));
        self::assertTrue($quota->raw['futureField']['preserved']);
    }

    public function testPreservesNullCreditDetailsAndMapsAnEmptyDetailList(): void
    {
        $mapper = new QuotaMapper;

        $countOnly = $mapper->map(['rateLimitResetCredits' => ['availableCount' => 3, 'credits' => null]]);
        $empty = $mapper->map(['rateLimitResetCredits' => ['availableCount' => 0, 'credits' => []]]);

        self::assertSame(3, $countOnly->resetCredits?->availableCount);
        self::assertNull($countOnly->resetCredits?->credits);
        self::assertSame([], $empty->resetCredits?->credits);
    }
}
