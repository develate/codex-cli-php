<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Parser;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\UnknownEvent;
use Develate\CodexCli\Exception\InvalidJsonlEvent;
use Develate\CodexCli\Parser\JsonlEventParser;
use Develate\CodexCli\Value\FileChangeKind;
use PHPUnit\Framework\TestCase;

final class JsonlEventParserTest extends TestCase
{
    public function testParsesKnownEventAndKeepsUnknownFields(): void
    {
        $events = (new JsonlEventParser())->parseLine(
            '{"type":"item.completed","future":true,"item":{"id":"a","type":"agent_message","text":"Hi","new_field":42}}',
        );

        self::assertCount(1, $events);
        self::assertInstanceOf(AgentMessage::class, $events[0]);
        self::assertSame('Hi', $events[0]->text);
        self::assertTrue($events[0]->raw()['future']);
        self::assertSame(42, $events[0]->raw()['item']['new_field']);
    }

    public function testUnknownEventsNeverFailParsing(): void
    {
        $events = (new JsonlEventParser())->parseLine('{"type":"future.event","payload":{"anything":true}}');

        self::assertInstanceOf(UnknownEvent::class, $events[0]);
        self::assertSame('future.event', $events[0]->type);
        self::assertTrue($events[0]->raw()['payload']['anything']);
    }

    public function testKeepsExecJsonEventsOnTheirExistingPublicClasses(): void
    {
        $parser = new JsonlEventParser();
        $message = $parser->parseLine('{"type":"item.completed","item":{"id":"message-1","type":"agent_message","text":"Done."}}');
        $completed = $parser->parseLine('{"type":"turn.completed","usage":{"input_tokens":13,"output_tokens":5}}');

        self::assertInstanceOf(AgentMessage::class, $message[0]);
        self::assertSame('Done.', $message[0]->text);
        self::assertInstanceOf(TurnCompleted::class, $completed[0]);
        self::assertSame(13, $completed[0]->usage->inputTokens);
        self::assertSame(5, $completed[0]->usage->outputTokens);
        self::assertNull($completed[0]->threadId);
        self::assertNull($completed[0]->turnId);
    }

    public function testExpandsEveryPathInAFileChangeItem(): void
    {
        $events = (new JsonlEventParser())->parseLine(
            '{"type":"item.completed","item":{"id":"f","type":"file_change","changes":['.
            '{"path":"src/A.php","kind":"add"},{"path":"src/B.php","kind":"update"}]}}',
        );

        self::assertCount(2, $events);
        self::assertContainsOnlyInstancesOf(FileChange::class, $events);
        self::assertSame('src/A.php', $events[0]->path);
        self::assertSame(FileChangeKind::Update, $events[1]->kind);
    }

    public function testRejectsMalformedJsonOnly(): void
    {
        $this->expectException(InvalidJsonlEvent::class);
        (new JsonlEventParser())->parseLine('{not json');
    }
}
