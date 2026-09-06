<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

/**
 * One thing the agent is asking permission for, in the vocabulary the caller
 * has to present to a person.
 */
final readonly class ApprovalRequest
{
    public const KIND_COMMAND = 'command';

    public const KIND_FILE_CHANGE = 'file_change';

    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $kind,
        public string $itemId,
        public string $subject,
        public array $payload,
        public ?string $callId = null,
    ) {
    }
}
