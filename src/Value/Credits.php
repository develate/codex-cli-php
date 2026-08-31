<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class Credits
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public bool $hasCredits,
        public bool $unlimited,
        public ?string $balance,
        public array $raw = [],
    ) {
    }
}
