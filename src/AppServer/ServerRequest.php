<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

/**
 * A request the app server sends to us.
 *
 * The direction that makes an interactive turn possible: the agent stops,
 * asks whether it may run a command or write a file, and waits for the answer
 * on the same connection. A request left unanswered blocks the turn forever,
 * so every one of these has to be replied to — including the ones this client
 * does not understand, which are refused rather than ignored.
 */
final readonly class ServerRequest
{
    /** @param array<string, mixed> $params */
    public function __construct(
        public string|int $id,
        public string $method,
        public array $params = [],
        public array $raw = [],
    ) {
    }

    /** @param array<string, mixed> $message */
    public static function fromMessage(array $message): self
    {
        return new self(
            id: is_string($message['id'] ?? null) || is_int($message['id'] ?? null) ? $message['id'] : '',
            method: is_string($message['method'] ?? null) ? $message['method'] : '',
            params: is_array($message['params'] ?? null) ? $message['params'] : [],
            raw: $message,
        );
    }
}
