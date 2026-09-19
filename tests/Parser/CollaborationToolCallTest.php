<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Parser;

use Develate\CodexCli\AppServer\Notification;
use Develate\CodexCli\AppServer\NotificationTranslator;
use Develate\CodexCli\Event\CollaborationToolCall;
use Develate\CodexCli\Parser\JsonlEventParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CollaborationToolCallTest extends TestCase
{
    /** @param array<string, mixed> $identity */
    #[DataProvider('collaborationItems')]
    public function testBothTransportPathsPreserveCollaboration(string $transport, string $phase, string $type, array $identity): void
    {
        $params = [
            'thread_id' => 'parent',
            'item' => [
                'type' => $type,
                'id' => 'spawn-1',
                'status' => $phase === 'completed' ? 'completed' : 'in_progress',
                'tool' => 'spawn_agent',
                'future_field' => ['untouched' => true],
                ...$identity,
            ],
        ];
        $parser = new JsonlEventParser();
        if ($transport === 'exec') {
            $raw = ['type' => 'item.'.$phase, ...$params, 'raw' => ['must_not_replace_envelope' => true]];
            $events = $parser->parseLine(json_encode($raw, JSON_THROW_ON_ERROR));
        } else {
            $raw = ['jsonrpc' => '2.0', 'method' => 'item/'.$phase, 'params' => $params];
            $events = $parser->parse((new NotificationTranslator())->translate(
                new Notification($raw['method'], $params, $raw),
            ));
        }

        self::assertCount(1, $events);
        $event = $events[0];
        self::assertInstanceOf(CollaborationToolCall::class, $event);
        self::assertSame('spawn-1', $event->id);
        self::assertSame('parent', $event->senderThreadId);
        self::assertSame(['child'], $event->receiverThreadIds);
        self::assertSame('requested-model', $event->requestedModel);
        self::assertSame($params['item']['status'], $event->status);
        self::assertSame('spawn_agent', $event->tool);
        self::assertSame($phase, $event->phase);
        self::assertSame($raw, $event->raw());
    }

    public static function collaborationItems(): iterable
    {
        $items = [
            'collab_tool_call' => ['sender_thread_id' => 'parent', 'receiver_thread_ids' => ['child', '', 'child'], 'requested_model' => 'requested-model'],
            'collab_agent_tool_call' => ['receiver_thread_id' => 'child', 'model' => 'requested-model'],
            'collaboration_tool_call' => ['receiverThreadIds' => ['child'], 'requestedModel' => 'requested-model'],
            'collabToolCall' => ['senderThreadId' => 'parent', 'receiverThreadId' => 'child', 'model' => 'requested-model'],
            'collabAgentToolCall' => ['childThreadId' => 'child', 'requestedModel' => 'requested-model'],
            'collaborationToolCall' => ['agentThreadIds' => ['child'], 'requested_model' => 'requested-model'],
        ];
        foreach (['exec', 'app-server'] as $transport) {
            foreach (['started', 'updated', 'completed'] as $phase) {
                foreach ($items as $type => $identity) {
                    yield "$transport $phase $type" => [$transport, $phase, $type, $identity];
                }
            }
        }
    }

    public function testPartialExecItemKeepsMissingIdentityEmpty(): void
    {
        $raw = ['type' => 'item.started', 'item' => ['type' => 'collab_tool_call', 'id' => 'spawn-1']];
        $event = (new JsonlEventParser())->parse($raw)[0];

        self::assertInstanceOf(CollaborationToolCall::class, $event);
        self::assertSame('', $event->senderThreadId);
        self::assertSame([], $event->receiverThreadIds);
        self::assertNull($event->requestedModel);
        self::assertNull($event->status);
        self::assertNull($event->tool);
        self::assertSame('started', $event->phase);
        self::assertSame($raw, $event->raw());
    }
}
