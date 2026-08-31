<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

use Develate\CodexCli\Value\FileChangeKind;

final readonly class FileChange implements CodexEvent
{
    /** @param array<string, mixed> $raw */
    public function __construct(
        public string $id,
        public string $path,
        public FileChangeKind $kind,
        public string $phase,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
