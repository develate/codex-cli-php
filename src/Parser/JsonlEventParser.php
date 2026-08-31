<?php

declare(strict_types=1);

namespace Develate\CodexCli\Parser;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\ErrorEvent;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\Reasoning;
use Develate\CodexCli\Event\ThreadStarted;
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
            'turn.started' => [new TurnStarted($data)],
            'turn.completed' => [new TurnCompleted(Usage::fromArray(self::map($data['usage'] ?? [])), $data)],
            'turn.failed' => [new TurnFailed(self::message($data), $data)],
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
}
