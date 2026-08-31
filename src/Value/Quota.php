<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class Quota
{
    /**
     * @param array<string, mixed> $additional
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public ?QuotaWindow $primary,
        public ?QuotaWindow $secondary,
        public array $additional,
        public ?Credits $credits,
        public array $raw = [],
    ) {
    }
}
