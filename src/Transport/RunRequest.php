<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\Value\Sandbox;

final readonly class RunRequest
{
    /**
     * @param list<string> $writableDirectories
     * @param list<string> $images
     * @param array<string, mixed>|null $schema
     */
    public function __construct(
        public RunMode $mode,
        public ?string $threadId,
        public string $prompt,
        public string $cwd,
        public ?string $model,
        public Sandbox $sandbox,
        public array $writableDirectories = [],
        public array $images = [],
        public ?array $schema = null,
        public ?float $timeout = null,
    ) {
    }
}
