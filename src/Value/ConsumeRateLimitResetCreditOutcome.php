<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

enum ConsumeRateLimitResetCreditOutcome: string
{
    case Reset = 'reset';
    case NothingToReset = 'nothingToReset';
    case NoCredit = 'noCredit';
    case AlreadyRedeemed = 'alreadyRedeemed';
}
