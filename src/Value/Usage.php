<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class Usage
{
    public function __construct(
        public int $inputTokens = 0,
        public int $cachedInputTokens = 0,
        public int $cacheWriteInputTokens = 0,
        public int $outputTokens = 0,
        public int $reasoningOutputTokens = 0,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: self::integer($data, 'input_tokens', 'inputTokens'),
            cachedInputTokens: self::integer($data, 'cached_input_tokens', 'cachedInputTokens'),
            cacheWriteInputTokens: self::integer($data, 'cache_write_input_tokens', 'cacheWriteInputTokens'),
            outputTokens: self::integer($data, 'output_tokens', 'outputTokens'),
            reasoningOutputTokens: self::integer($data, 'reasoning_output_tokens', 'reasoningOutputTokens'),
        );
    }

    public function totalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }

    public function uncachedInputTokens(): int
    {
        return max(0, $this->inputTokens - $this->cachedInputTokens);
    }

    public function cacheHitRatio(): float
    {
        return $this->inputTokens === 0 ? 0.0 : $this->cachedInputTokens / $this->inputTokens;
    }

    /** @param array<string, mixed> $data */
    private static function integer(array $data, string $snake, string $camel): int
    {
        $value = $data[$snake] ?? $data[$camel] ?? 0;

        return is_numeric($value) ? (int) $value : 0;
    }
}
