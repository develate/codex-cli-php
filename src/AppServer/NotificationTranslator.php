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
 * A notification with no JSONL counterpart returns null and is dropped: the
 * app server emits a great deal that the exec stream never had, and inventing
 * events for it would put entries in the transcript that no other run produces.
 */
final class NotificationTranslator
{
    /**
     * @return array<string, mixed>|null
     */
    public function translate(Notification $notification): ?array
    {
        $params = $notification->params;

        return match ($notification->method) {
            'thread/started' => [
                'type' => 'thread.started',
                'thread_id' => $this->string($this->map($params['thread'] ?? [])['id'] ?? ''),
            ],
            'turn/started' => ['type' => 'turn.started'],
            'turn/completed' => $this->turnCompleted($params),
            'error' => [
                'type' => 'error',
                'message' => $this->string($this->map($params['error'] ?? [])['message'] ?? ''),
            ],
            'item/started' => $this->item($params, 'started'),
            'item/completed' => $this->item($params, 'completed'),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function turnCompleted(array $params): array
    {
        $turn = $this->map($params['turn'] ?? []);
        $error = $this->map($turn['error'] ?? []);

        // A turn the server reports as failed is a failure even though the
        // notification that carried it is the completion one.
        if (($turn['status'] ?? null) === 'failed' || $error !== []) {
            return [
                'type' => 'turn.failed',
                'message' => $this->string($error['message'] ?? 'The turn failed.'),
            ];
        }

        return [
            'type' => 'turn.completed',
            'usage' => $this->map($turn['usage'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    private function item(array $params, string $phase): ?array
    {
        $item = $this->map($params['item'] ?? []);
        $type = $this->string($item['type'] ?? '');

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
                'status' => $item['status'] ?? null,
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
            return null;
        }

        return ['type' => 'item.'.$phase, 'item' => $translated];
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

            // The app server keys changes by path; the JSONL stream carries the
            // path inside each entry.
            $translated[] = [
                'path' => is_string($path) ? $path : $this->string($change['path'] ?? ''),
                'kind' => $change['kind'] ?? ($change['type'] ?? null),
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
}
