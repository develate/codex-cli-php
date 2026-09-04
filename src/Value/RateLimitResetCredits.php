<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RateLimitResetCredits
{
    /** @param list<RateLimitResetCredit>|null $credits */
    public function __construct(
        public int $availableCount,
        public ?array $credits,
    ) {
    }
}
