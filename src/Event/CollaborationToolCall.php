<?php

declare(strict_types=1);

namespace Develate\CodexCli\Event;

/**
 * A collaboration-tool item emitted by the Codex app server.
 *
 * Codex has used both `collabToolCall` and `collabAgentToolCall` for this
 * item over time. The transport normalizes their shared protocol facts here
 * without hiding the version-specific payload available through raw().
 */
final readonly class CollaborationToolCall implements CodexEvent
{
    /**
     * @param list<string> $receiverThreadIds
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public string $senderThreadId,
        public array $receiverThreadIds,
        public ?string $requestedModel,
        public ?string $status,
        public ?string $tool,
        public string $phase,
        private array $raw,
    ) {
    }

    public function raw(): array
    {
        return $this->raw;
    }
}
