<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RealtimeTransport
{
    private function __construct(private string $type, private ?string $value = null)
    {
    }

    public static function websocket(): self
    {
        return new self('websocket');
    }

    public static function webrtc(string $sdp): self
    {
        if ($sdp === '') {
            throw new \InvalidArgumentException('The WebRTC SDP offer must not be empty.');
        }

        return new self('webrtc', $sdp);
    }

    public static function existingCall(string $callId): self
    {
        if ($callId === '') {
            throw new \InvalidArgumentException('The realtime call ID must not be empty.');
        }

        return new self('existingCall', $callId);
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return match ($this->type) {
            'websocket' => ['type' => 'websocket'],
            'webrtc' => ['type' => 'webrtc', 'sdp' => $this->value],
            'existingCall' => ['type' => 'existingCall', 'callId' => $this->value],
        };
    }
}
