<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class ConsumeRateLimitResetCreditResult
{
    public function __construct(public ConsumeRateLimitResetCreditOutcome $outcome)
    {
    }
}
