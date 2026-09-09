<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RealtimeAudioChunk
{
    public function __construct(
        public string $data,
        public int $sampleRate,
        public int $numChannels,
        public ?int $samplesPerChannel = null,
        public ?string $itemId = null,
    ) {
        if ($data === '' || $sampleRate < 1 || $numChannels < 1) {
            throw new \InvalidArgumentException('A realtime audio chunk needs data, sample rate, and channels.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'data' => $this->data,
            'sampleRate' => $this->sampleRate,
            'numChannels' => $this->numChannels,
            'samplesPerChannel' => $this->samplesPerChannel,
            'itemId' => $this->itemId,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /** @param array<string, mixed> $value */
    public static function fromArray(array $value): ?self
    {
        return is_string($value['data'] ?? null) && is_int($value['sampleRate'] ?? null)
            && is_int($value['numChannels'] ?? null)
            ? new self(
                $value['data'],
                $value['sampleRate'],
                $value['numChannels'],
                is_int($value['samplesPerChannel'] ?? null) ? $value['samplesPerChannel'] : null,
                is_string($value['itemId'] ?? null) ? $value['itemId'] : null
            )
            : null;
    }
}
