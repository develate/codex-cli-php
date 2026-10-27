<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\AppServer;

use Develate\CodexCli\AppServer\Notification;
use Develate\CodexCli\AppServer\NotificationTranslator;
use Develate\CodexCli\Event\AccountRateLimitsUpdated;
use Develate\CodexCli\Event\UnknownEvent;
use Develate\CodexCli\Parser\JsonlEventParser;
use PHPUnit\Framework\TestCase;

final class RateLimitsUpdatedTest extends TestCase
{
    public function testNotificationProducesTypedQuotaAndPreservesRawData(): void
    {
        $params = ['rateLimits' => [
            'primary' => ['usedPercent' => 25, 'windowDurationMins' => 300, 'resetsAt' => 1800000000],
            'secondary' => ['usedPercent' => 60],
            'credits' => ['hasCredits' => true, 'balance' => '12.50'],
            'planType' => 'pro',
        ]];
        $raw = ['method' => 'account/rateLimits/updated', 'params' => $params];
        $translated = (new NotificationTranslator())->translate(new Notification($raw['method'], $params, $raw));

        $event = (new JsonlEventParser())->parse($translated)[0];

        self::assertInstanceOf(AccountRateLimitsUpdated::class, $event);
        self::assertSame(25.0, $event->quota->primary->usedPercent);
        self::assertSame(300, $event->quota->primary->windowMinutes);
        self::assertSame(1800000000, $event->quota->primary->resetsAt->getTimestamp());
        self::assertSame(60.0, $event->quota->secondary->usedPercent);
        self::assertSame('12.50', $event->quota->credits->balance);
        self::assertSame($raw, $event->raw());
    }

    public function testMalformedNotificationRemainsUnknown(): void
    {
        foreach ([[], ['rateLimits' => null], ['rateLimits' => 'invalid'], ['rateLimits' => []]] as $params) {
            $raw = ['method' => 'account/rateLimits/updated', 'params' => $params];
            $translated = (new NotificationTranslator())->translate(new Notification($raw['method'], $params, $raw));
            $event = (new JsonlEventParser())->parse($translated)[0];

            self::assertInstanceOf(UnknownEvent::class, $event);
            self::assertSame($raw, $event->raw());
        }
    }
}
