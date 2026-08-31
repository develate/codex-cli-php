<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

final readonly class Response
{
    /**
     * @param array<string, mixed>|null $error
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public int $id,
        public mixed $result,
        public ?array $error,
        public array $raw,
    ) {
    }
}
