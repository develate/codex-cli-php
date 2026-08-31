<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class Account
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public ?string $type,
        public ?string $email,
        public ?string $planType,
        public bool $requiresOpenaiAuth,
        public array $raw = [],
    ) {
    }
}
