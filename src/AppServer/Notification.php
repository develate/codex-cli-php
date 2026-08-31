<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

final readonly class Notification
{
    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $raw
     */
    public function __construct(public string $method, public array $params, public array $raw)
    {
    }
}
