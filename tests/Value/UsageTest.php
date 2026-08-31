<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Value;

use Develate\CodexCli\Value\Usage;
use PHPUnit\Framework\TestCase;

final class UsageTest extends TestCase
{
    public function testConvenienceCalculations(): void
    {
        $usage = new Usage(inputTokens: 100, cachedInputTokens: 75, outputTokens: 20);

        self::assertSame(120, $usage->totalTokens());
        self::assertSame(25, $usage->uncachedInputTokens());
        self::assertSame(0.75, $usage->cacheHitRatio());
    }
}
