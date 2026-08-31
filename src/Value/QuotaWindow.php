<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class QuotaWindow
{
    public function __construct(
        public float $usedPercent,
        public ?int $windowMinutes,
        public ?\DateTimeImmutable $resetsAt,
    ) {
    }

    public function remainingPercent(): float
    {
        return max(0.0, 100.0 - $this->usedPercent);
    }
}
