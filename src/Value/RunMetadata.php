<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RunMetadata
{
    public function __construct(
        public string $codexVersion,
        public ?string $requestedModel,
        public string $cwd,
    ) {
    }
}
