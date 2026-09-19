<?php

declare(strict_types=1);

namespace Develate\CodexCli\Parser;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CollaborationToolCall;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\ErrorEvent;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\ModelRerouted;
use Develate\CodexCli\Event\Reasoning;
use Develate\CodexCli\Event\ThreadStarted;
use Develate\CodexCli\Event\ThreadTokenUsageUpdated;
use Develate\CodexCli\Event\TodoList;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\TurnFailed;
use Develate\CodexCli\Event\TurnStarted;
use Develate\CodexCli\Event\UnknownEvent;
use Develate\CodexCli\Exception\InvalidJsonlEvent;
use Develate\CodexCli\Value\CommandStatus;
use Develate\CodexCli\Value\FileChangeKind;
use Develate\CodexCli\Value\Usage;

final class JsonlEventParser
{
    /** @return list<CodexEvent> */
    public function parseLine(string $line): array
    {
        try {
            $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidJsonlEvent($line, $exception);
        }

        if (!is_array($data)) {
            throw new InvalidJsonlEvent($line, new \UnexpectedValueException('Expected a JSON object.'));
        }

        /** @var array<string, mixed> $data */
        return $this->parse($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return list<CodexEvent>
     */
    public function parse(array $data): array
    {
        $type = is_string($data['type'] ?? null) ? $data['type'] : '';

        return match ($type) {
            'thread.started' => [new ThreadStarted(self::string($data['thread_id'] ?? $data['threadId'] ?? ''), $data)],
            'turn.started' => [new TurnStarted($data, self::nullableString($data['thread_id'] ?? $data['threadId'] ?? null), self::nullableString($data['turn_id'] ?? $data['turnId'] ?? null))],
            'turn.completed' => [new TurnCompleted(
                Usage::fromArray(self::map($data['usage'] ?? [])),
                $data,
                self::nullableString($data['thread_id'] ?? $data['threadId'] ?? null),
                self::nullableString($data['turn_id'] ?? $data['turnId'] ?? null),
            )],
            'turn.failed' => [new TurnFailed(
                self::message($data),
                $data,
                self::nullableString($data['thread_id'] ?? $data['threadId'] ?? null),
                self::nullableString($data['turn_id'] ?? $data['turnId'] ?? null),
            )],
            'thread.token_usage.updated' => [new ThreadTokenUsageUpdated(
                threadId: self::string($data['thread_id'] ?? $data['threadId'] ?? ''),
                turnId: self::string($data['turn_id'] ?? $data['turnId'] ?? ''),
                last: Usage::fromArray(self::map($data['last_usage'] ?? $data['lastUsage'] ?? [])),
                total: Usage::fromArray(self::map($data['total_usage'] ?? $data['totalUsage'] ?? [])),
                raw: self::appServerRaw($data),
            )],
            'collaboration.tool_call' => [new CollaborationToolCall(
                id: self::string($data['id'] ?? ''),
                senderThreadId: self::string($data['sender_thread_id'] ?? $data['senderThreadId'] ?? ''),
                receiverThreadIds: self::strings($data['receiver_thread_ids'] ?? $data['receiverThreadIds'] ?? []),
                requestedModel: self::nullableString($data['requested_model'] ?? $data['requestedModel'] ?? null),
                status: self::nullableString($data['status'] ?? null),
                tool: self::nullableString($data['tool'] ?? null),
                phase: self::string($data['phase'] ?? ''),
                raw: self::appServerRaw($data),
            )],
            'model.rerouted' => [new ModelRerouted(
                threadId: self::string($data['thread_id'] ?? $data['threadId'] ?? ''),
                turnId: self::string($data['turn_id'] ?? $data['turnId'] ?? ''),
                fromModel: self::string($data['from_model'] ?? $data['fromModel'] ?? ''),
                toModel: self::string($data['to_model'] ?? $data['toModel'] ?? ''),
                reason: self::nullableString($data['reason'] ?? null),
                raw: self::appServerRaw($data),
            )],
            'app_server.unknown' => [new UnknownEvent(
                self::string($data['method'] ?? 'app_server.unknown'),
                self::appServerRaw($data),
            )],
            'error' => [new ErrorEvent(self::message($data), $data)],
            'item.started', 'item.updated', 'item.completed' => $this->parseItem($data, substr($type, 5)),
            default => [new UnknownEvent($type, $data)],
        };
    }

    /**
     * @param array<string, mixed> $event
     * @return list<CodexEvent>
     */
    private function parseItem(array $event, string $phase): array
    {
        $item = self::map($event['item'] ?? []);
        $itemType = is_string($item['type'] ?? null) ? $item['type'] : '';
        $id = self::string($item['id'] ?? '');

        return match ($itemType) {
            'collab_tool_call', 'collab_agent_tool_call', 'collaboration_tool_call',
            'collabToolCall', 'collabAgentToolCall', 'collaborationToolCall' => [new CollaborationToolCall(
                id: self::string($item['id'] ?? $item['callId'] ?? $event['itemId'] ?? $event['id'] ?? ''),
                senderThreadId: self::string($item['sender_thread_id'] ?? $item['senderThreadId'] ?? $event['sender_thread_id'] ?? $event['senderThreadId'] ?? $event['thread_id'] ?? $event['threadId'] ?? ''),
                receiverThreadIds: self::strings(
                    $item['receiver_thread_ids'] ?? $item['receiverThreadIds']
                        ?? $item['receiver_thread_id'] ?? $item['receiverThreadId']
                        ?? $item['child_thread_ids'] ?? $item['childThreadIds']
                        ?? $item['child_thread_id'] ?? $item['childThreadId']
                        ?? $item['agent_thread_ids'] ?? $item['agentThreadIds']
                        ?? $item['agent_thread_id'] ?? $item['agentThreadId']
                        ?? $event['receiver_thread_ids'] ?? $event['receiverThreadIds']
                        ?? $event['receiver_thread_id'] ?? $event['receiverThreadId']
                        ?? $event['child_thread_ids'] ?? $event['childThreadIds']
                        ?? $event['child_thread_id'] ?? $event['childThreadId'] ?? [],
                ),
                requestedModel: self::nullableString($item['requested_model'] ?? $item['requestedModel'] ?? $item['model'] ?? $event['requested_model'] ?? $event['requestedModel'] ?? $event['model'] ?? null),
                status: self::nullableString($item['status'] ?? $event['status'] ?? null),
                tool: self::nullableString($item['tool'] ?? $event['tool'] ?? null),
                phase: $phase,
                raw: $event,
            )],
            'agent_message' => [new AgentMessage($id, self::string($item['text'] ?? $item['message'] ?? ''), $phase, $event)],
            'reasoning' => [new Reasoning($id, self::string($item['text'] ?? $item['summary'] ?? ''), $phase, $event)],
            'command_execution' => [new CommandExecution(
                id: $id,
                command: self::string($item['command'] ?? ''),
                status: CommandStatus::fromCodex($item['status'] ?? null),
                output: self::string($item['aggregated_output'] ?? $item['output'] ?? ''),
                exitCode: is_numeric($item['exit_code'] ?? null) ? (int) $item['exit_code'] : null,
                phase: $phase,
                raw: $event,
            )],
            'file_change' => $this->parseFileChanges($event, $item, $id, $phase),
            'todo_list', 'plan_update' => [new TodoList($id, self::listOfMaps($item['items'] ?? $item['todos'] ?? []), $phase, $event)],
            default => [new UnknownEvent($itemType === '' ? (string) ($event['type'] ?? '') : $itemType, $event)],
        };
    }

    /**
     * @param array<string, mixed> $event
     * @param array<string, mixed> $item
     * @return list<CodexEvent>
     */
    private function parseFileChanges(array $event, array $item, string $id, string $phase): array
    {
        $changes = self::listOfMaps($item['changes'] ?? []);
        if ($changes === []) {
            $changes = [[
                'path' => $item['path'] ?? '',
                'kind' => $item['kind'] ?? null,
            ]];
        }

        $events = [];
        foreach ($changes as $change) {
            $events[] = new FileChange(
                id: $id,
                path: self::string($change['path'] ?? ''),
                kind: FileChangeKind::fromCodex($change['kind'] ?? null),
                phase: $phase,
                raw: $event,
            );
        }

        return $events;
    }

    /** @param array<string, mixed> $data */
    private static function message(array $data): string
    {
        $error = self::map($data['error'] ?? []);

        return self::string($data['message'] ?? $error['message'] ?? $data['detail'] ?? '');
    }

    /** @return array<string, mixed> */
    private static function map(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @return list<array<string, mixed>> */
    private static function listOfMaps(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_array'));
    }

    private static function string(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            $parts = [];
            array_walk_recursive($value, static function (mixed $part) use (&$parts): void {
                if (is_scalar($part)) {
                    $parts[] = (string) $part;
                }
            });

            return implode("\n", $parts);
        }

        return '';
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = self::string($value);

        return $value === '' ? null : $value;
    }

    /** @return list<string> */
    private static function strings(mixed $value): array
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        $strings = [];
        foreach ($value as $item) {
            $item = self::string($item);
            if ($item !== '') {
                $strings[] = $item;
            }
        }

        return array_values(array_unique($strings));
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private static function appServerRaw(array $data): array
    {
        return self::map($data['raw'] ?? $data);
    }
}
