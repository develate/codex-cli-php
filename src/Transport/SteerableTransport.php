<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

interface SteerableTransport extends Transport
{
    /** @param list<string> $images */
    public function steer(string $prompt, array $images = []): string;
}
