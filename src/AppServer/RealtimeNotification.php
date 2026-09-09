<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Value\RealtimeAudioChunk;

final readonly class RealtimeNotification
{
    /** @param array<string, mixed> $params @param array<string, mixed> $raw */
    public function __construct(
        public string $method,
        public string $threadId,
        public array $params,
        public ?string $realtimeSessionId = null,
        public ?string $role = null,
        public ?string $delta = null,
        public ?string $text = null,
        public ?RealtimeAudioChunk $audio = null,
        public ?string $sdp = null,
        public ?string $message = null,
        public ?string $reason = null,
        public mixed $item = null,
        public array $raw = [],
        public ?string $version = null,
        public ?string $itemId = null,
    ) {
    }

    public static function from(Notification $notification): self
    {
        $p = $notification->params;
        $audio = is_array($p['audio'] ?? null) ? RealtimeAudioChunk::fromArray($p['audio']) : null;

        return new self(
            method: $notification->method,
            threadId: is_string($p['threadId'] ?? null) ? $p['threadId'] : '',
            params: $p,
            realtimeSessionId: is_string($p['realtimeSessionId'] ?? null) ? $p['realtimeSessionId'] : null,
            role: is_string($p['role'] ?? null) ? $p['role'] : null,
            delta: is_string($p['delta'] ?? null) ? $p['delta'] : null,
            text: is_string($p['text'] ?? null) ? $p['text'] : null,
            audio: $audio,
            sdp: is_string($p['sdp'] ?? null) ? $p['sdp'] : null,
            message: is_string($p['message'] ?? null) ? $p['message'] : null,
            reason: is_string($p['reason'] ?? null) ? $p['reason'] : null,
            item: $p['item'] ?? null,
            raw: $notification->raw,
            version: is_string($p['version'] ?? null) ? $p['version'] : null,
            itemId: is_string($p['itemId'] ?? null) ? $p['itemId'] : null,
        );
    }
}
