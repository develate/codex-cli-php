<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

/**
 * Turns app-server notifications into the event shape `codex exec --json`
 * produces.
 *
 * Both transports describe the same conversation, but the app server speaks
 * camelCase and namespaces its methods with slashes, while the JSONL stream
 * uses dotted types and snake_case fields. Translating here rather than
 * teaching the parser two dialects means every consumer downstream — the event
 * parser, the normalizer, the UI — keeps working unchanged whichever transport
 * ran the turn.
 *
 * App-server-only facts stay typed when the protocol identifies them. Unknown
 * notifications are deliberately retained as raw events so a newer Codex CLI
 * cannot become silent data loss for callers that stream a turn.
 */
final class NotificationTranslator
{
    /**
     * @return array<string, mixed>
     */
    public function translate(Notification $notification): array
    {
        $params = $notification->params;

        $event = match ($notification->method) {
            'account/rateLimits/updated' => is_array($params['rateLimits'] ?? null) && $params['rateLimits'] !== []
                ? ['type' => 'account.rate_limits.updated', 'rate_limits' => $params, 'raw' => $notification->raw]
                : $this->unknown($notification),
            'thread/started' => $this->threadStarted($params),
            'turn/started' => $this->turnStarted($params),
            'turn/completed' => $this->turnCompleted($params),
            'thread/tokenUsage/updated', 'thread/token_usage/updated' => $this->tokenUsageUpdated($params, $notification->raw),
            'model/rerouted', 'thread/model/rerouted' => $this->modelRerouted($params, $notification->raw),
            'error' => [
                'type' => 'error',
                'message' => $this->string($this->map($params['error'] ?? [])['message'] ?? ''),
            ],
            'item/started' => $this->item($notification, 'started'),
            'item/completed' => $this->item($notification, 'completed'),
            'item/updated' => $this->item($notification, 'updated'),
            default => $this->unknown($notification),
        };

        $event['raw'] = $notification->raw;

        return $event;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function threadStarted(array $params): array
    {
        $thread = $this->map($params['thread'] ?? []);

        return [
            'type' => 'thread.started',
            'thread_id' => $this->string($thread['id'] ?? $params['threadId'] ?? $params['thread_id'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function turnStarted(array $params): array
    {
        $turn = $this->map($params['turn'] ?? []);

        return [
            'type' => 'turn.started',
            'thread_id' => $this->string($params['threadId'] ?? $params['thread_id'] ?? ''),
            'turn_id' => $this->string($turn['id'] ?? $params['turnId'] ?? $params['turn_id'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function turnCompleted(array $params): array
    {
        $turn = $this->map($params['turn'] ?? []);
        $error = $this->map($turn['error'] ?? []);
        $threadId = $this->string($params['threadId'] ?? $params['thread_id'] ?? '');
        $turnId = $this->string($turn['id'] ?? $params['turnId'] ?? $params['turn_id'] ?? '');
        $status = $this->string($turn['status'] ?? 'completed');

        // Completion is terminal, but only the completed status is success.
        if ($status !== 'completed' || $error !== []) {
            return [
                'type' => 'turn.failed',
                'message' => $this->string($error['message'] ?? ($status === 'interrupted' ? 'The turn was interrupted.' : 'The turn failed.')),
                'status' => $status,
                'thread_id' => $threadId,
                'turn_id' => $turnId,
            ];
        }

        return [
            'type' => 'turn.completed',
            'usage' => $this->map($turn['usage'] ?? []),
            'thread_id' => $threadId,
            'turn_id' => $turnId,
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function tokenUsageUpdated(array $params, array $raw): array
    {
        $usage = $this->map($params['tokenUsage'] ?? $params['token_usage'] ?? []);

        return [
            'type' => 'thread.token_usage.updated',
            'thread_id' => $this->string($params['threadId'] ?? $params['thread_id'] ?? ''),
            'turn_id' => $this->string($params['turnId'] ?? $params['turn_id'] ?? ''),
            'last_usage' => $this->map($usage['last'] ?? $usage['lastUsage'] ?? []),
            'total_usage' => $this->map($usage['total'] ?? $usage['totalUsage'] ?? []),
            'raw' => $raw,
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function modelRerouted(array $params, array $raw): array
    {
        return [
            'type' => 'model.rerouted',
            'thread_id' => $this->string($params['threadId'] ?? $params['thread_id'] ?? ''),
            'turn_id' => $this->string($params['turnId'] ?? $params['turn_id'] ?? ''),
            'from_model' => $this->string($params['fromModel'] ?? $params['from_model'] ?? ''),
            'to_model' => $this->string($params['toModel'] ?? $params['to_model'] ?? ''),
            'reason' => $this->nullableString($params['reason'] ?? null),
            'raw' => $raw,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function item(Notification $notification, string $phase): array
    {
        $params = $notification->params;
        $item = $this->map($params['item'] ?? []);
        $type = $this->string($item['type'] ?? '');

        if ($this->isCollaborationToolCall($type)) {
            return $this->collaborationToolCall($params, $item, $phase, $notification->raw);
        }

        $translated = match ($type) {
            'agentMessage' => ['type' => 'agent_message', 'id' => $item['id'] ?? '', 'text' => $item['text'] ?? ''],
            'reasoning' => [
                'type' => 'reasoning',
                'id' => $item['id'] ?? '',
                'text' => $item['summary'] ?? $item['content'] ?? '',
            ],
            'plan' => ['type' => 'todo_list', 'id' => $item['id'] ?? '', 'items' => $item['items'] ?? []],
            'commandExecution' => [
                'type' => 'command_execution',
                'id' => $item['id'] ?? '',
                'command' => $item['command'] ?? '',
                'status' => ($item['status'] ?? null) === 'inProgress' ? 'in_progress' : ($item['status'] ?? null),
                'aggregated_output' => $item['aggregatedOutput'] ?? '',
                'exit_code' => $item['exitCode'] ?? null,
            ],
            'fileChange' => [
                'type' => 'file_change',
                'id' => $item['id'] ?? '',
                'changes' => $this->changes($item['changes'] ?? []),
            ],
            default => null,
        };

        if ($translated === null) {
            return $this->unknown($notification);
        }

        return ['type' => 'item.'.$phase, 'item' => $translated];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $item
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function collaborationToolCall(array $params, array $item, string $phase, array $raw): array
    {
        return [
            'type' => 'collaboration.tool_call',
            'id' => $this->string($item['id'] ?? $item['callId'] ?? $params['itemId'] ?? $params['id'] ?? ''),
            'sender_thread_id' => $this->string(
                $item['senderThreadId']
                    ?? $item['sender_thread_id']
                    ?? $params['senderThreadId']
                    ?? $params['sender_thread_id']
                    ?? $params['threadId']
                    ?? $params['thread_id']
                    ?? '',
            ),
            'receiver_thread_ids' => $this->strings(
                $item['receiverThreadIds']
                    ?? $item['receiver_thread_ids']
                    ?? $item['receiverThreadId']
                    ?? $item['receiver_thread_id']
                    ?? $item['childThreadIds']
                    ?? $item['childThreadId']
                    ?? $item['agentThreadIds']
                    ?? $item['agentThreadId']
                    ?? $params['receiverThreadIds']
                    ?? $params['receiver_thread_ids']
                    ?? $params['receiverThreadId']
                    ?? $params['receiver_thread_id']
                    ?? $params['childThreadIds']
                    ?? $params['childThreadId']
                    ?? [],
            ),
            'requested_model' => $this->nullableString(
                $item['requestedModel']
                    ?? $item['requested_model']
                    ?? $item['model']
                    ?? $params['requestedModel']
                    ?? $params['requested_model']
                    ?? $params['model']
                    ?? null,
            ),
            'status' => $this->nullableString($item['status'] ?? $params['status'] ?? null),
            'tool' => $this->nullableString($item['tool'] ?? $params['tool'] ?? null),
            'phase' => $phase,
            'raw' => $raw,
        ];
    }

    /** @return array<string, mixed> */
    private function unknown(Notification $notification): array
    {
        return [
            'type' => 'app_server.unknown',
            'method' => $notification->method,
            'raw' => $notification->raw,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function changes(mixed $changes): array
    {
        if (!is_array($changes)) {
            return [];
        }

        $translated = [];

        foreach ($changes as $path => $change) {
            $change = $this->map($change);
            $kind = $change['kind'] ?? ($change['type'] ?? null);

            // Current servers send a list with object kinds; older payloads
            // may key changes by path and use string kinds.
            $translated[] = [
                'path' => is_string($path) ? $path : $this->string($change['path'] ?? ''),
                'kind' => is_array($kind) ? ($kind['type'] ?? null) : $kind,
            ];
        }

        return $translated;
    }

    /** @return array<string, mixed> */
    private function map(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->string($value);

        return $value === '' ? null : $value;
    }

    private function isCollaborationToolCall(string $type): bool
    {
        return in_array(strtolower($type), [
            'collabtoolcall',
            'collabagenttoolcall',
            'collaborationtoolcall',
            'collab_tool_call',
            'collab_agent_tool_call',
            'collaboration_tool_call',
        ], true);
    }

    /** @return list<string> */
    private function strings(mixed $values): array
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        $strings = [];
        foreach ($values as $value) {
            $value = $this->string($value);
            if ($value !== '') {
                $strings[] = $value;
            }
        }

        return array_values(array_unique($strings));
    }
}
