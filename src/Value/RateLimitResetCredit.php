<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RateLimitResetCredit
{
    public function __construct(
        public string $id,
        public string $status,
        public string $resetType,
        public \DateTimeImmutable $grantedAt,
        public ?\DateTimeImmutable $expiresAt,
    ) {
    }
}
