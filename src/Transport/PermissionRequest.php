<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

/**
 * A request from the agent for additional sandbox permissions.
 *
 * Unlike command and file-change approvals, this request is answered with the
 * subset of the permission profile the user grants rather than a decision
 * string. The payload remains available to callers for display and logging.
 */
final readonly class PermissionRequest
{
    public const METHOD = 'item/permissions/requestApproval';

    /**
     * @param  array<string, mixed>  $permissions
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $threadId,
        public string $turnId,
        public string $itemId,
        public ?string $environmentId,
        public int $startedAtMs,
        public string $cwd,
        public ?string $reason,
        public array $permissions,
        public array $payload = [],
    ) {}

    /**
     * Parses the current app-server permission-request payload.
     *
     * Returning null for an incomplete payload keeps the transport fail-closed:
     * it cannot safely present or answer a request whose requested profile is
     * missing.
     *
     * @param  array<string, mixed>  $params
     */
    public static function fromParams(array $params): ?self
    {
        $threadId = self::requiredText($params, 'threadId');
        $turnId = self::requiredText($params, 'turnId');
        $itemId = self::requiredText($params, 'itemId');
        $cwd = self::requiredText($params, 'cwd');
        $startedAtMs = $params['startedAtMs'] ?? null;
        $permissions = $params['permissions'] ?? null;

        if ($threadId === null || $turnId === null || $itemId === null || $cwd === null
            || ! is_int($startedAtMs) || ! is_array($permissions)) {
            return null;
        }

        $environmentId = $params['environmentId'] ?? null;
        $reason = $params['reason'] ?? null;

        if ($environmentId !== null && ! is_string($environmentId)) {
            return null;
        }

        if ($reason !== null && ! is_string($reason)) {
            return null;
        }

        return new self(
            threadId: $threadId,
            turnId: $turnId,
            itemId: $itemId,
            environmentId: $environmentId,
            startedAtMs: $startedAtMs,
            cwd: $cwd,
            reason: $reason,
            permissions: $permissions,
            payload: $params,
        );
    }

    /** @param array<string, mixed> $params */
    private static function requiredText(array $params, string $key): ?string
    {
        return is_string($params[$key] ?? null) ? $params[$key] : null;
    }
}
