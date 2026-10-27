<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

use Develate\CodexCli\Value\Quota;

/** A sparse account quota update; merge available fields with the last full read. */
final readonly class AccountRateLimitsUpdated implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(public Quota $quota, private array $raw)
    {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
