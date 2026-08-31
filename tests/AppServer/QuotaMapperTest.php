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
            'futureField' => ['preserved' => true],
        ]);

        self::assertSame(75.0, $quota->primary?->remainingPercent());
        self::assertSame(15, $quota->primary?->windowMinutes);
        self::assertTrue($quota->credits?->hasCredits);
        self::assertSame('12.50', $quota->credits?->balance);
        self::assertSame(58.0, $quota->additional['other']->primary?->remainingPercent());
        self::assertTrue($quota->raw['futureField']['preserved']);
    }
}
