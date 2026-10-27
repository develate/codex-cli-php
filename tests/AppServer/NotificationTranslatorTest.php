<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\AppServer;

use Develate\CodexCli\AppServer\Notification;
use Develate\CodexCli\AppServer\NotificationTranslator;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\FileChange;
use Develate\CodexCli\Event\TurnFailed;
use Develate\CodexCli\Parser\JsonlEventParser;
use Develate\CodexCli\Value\CommandStatus;
use Develate\CodexCli\Value\FileChangeKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationTranslatorTest extends TestCase
{
    #[DataProvider('commandStatuses')]
    public function testCommandStatusesMatchStableAppServerValues(string $status, CommandStatus $expected): void
    {
        $raw = $this->notification('item/started', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'item' => [
            'type' => 'commandExecution', 'id' => 'command-1', 'command' => 'pwd',
            'cwd' => '/project', 'status' => $status, 'aggregatedOutput' => null,
            'exitCode' => null, 'commandActions' => [], 'futureField' => 42,
        ]]);

        $event = $this->parse($raw)[0];

        self::assertInstanceOf(CommandExecution::class, $event);
        self::assertSame($expected, $event->status);
        self::assertSame($raw, $event->raw());
    }

    public static function commandStatuses(): iterable
    {
        yield 'in progress' => ['inProgress', CommandStatus::InProgress];
        yield 'completed' => ['completed', CommandStatus::Completed];
        yield 'failed' => ['failed', CommandStatus::Failed];
        yield 'declined' => ['declined', CommandStatus::Declined];
        yield 'future status' => ['futureStatus', CommandStatus::Unknown];
    }

    public function testPatchObjectsKeepTheirKindsAndOriginalDiffs(): void
    {
        $raw = $this->notification('item/completed', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'item' => [
            'type' => 'fileChange', 'id' => 'patch-1', 'status' => 'completed', 'changes' => [
                ['path' => 'new.php', 'kind' => ['type' => 'add'], 'diff' => '+new'],
                ['path' => 'old.php', 'kind' => ['type' => 'delete'], 'diff' => '-old'],
                ['path' => 'edit.php', 'kind' => ['type' => 'update', 'move_path' => 'renamed.php'], 'diff' => '-old\n+new'],
                ['path' => 'future.php', 'kind' => ['type' => 'future'], 'diff' => 'future diff'],
            ],
        ]]);

        $events = $this->parse($raw);

        self::assertContainsOnlyInstancesOf(FileChange::class, $events);
        self::assertSame(['new.php', 'old.php', 'edit.php', 'future.php'], array_column($events, 'path'));
        self::assertSame([FileChangeKind::Add, FileChangeKind::Delete, FileChangeKind::Update, FileChangeKind::Unknown], array_column($events, 'kind'));
        foreach ($events as $event) {
            self::assertSame($raw, $event->raw());
        }
    }

    #[DataProvider('knownNotifications')]
    public function testKnownNotificationsKeepTheirOriginalPayload(string $method, array $params): void
    {
        $raw = $this->notification($method, $params);

        foreach ($this->parse($raw) as $event) {
            self::assertSame($raw, $event->raw());
        }
    }

    public static function knownNotifications(): iterable
    {
        yield 'thread' => ['thread/started', ['thread' => ['id' => 'thread-1', 'future' => true]]];
        yield 'turn start' => ['turn/started', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'inProgress']]];
        yield 'turn complete' => ['turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => [], 'error' => null]]];
        yield 'error' => ['error', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'willRetry' => true, 'error' => ['message' => 'Unavailable', 'codexErrorInfo' => 'flexUnavailable', 'additionalDetails' => 'retry later']]];
        yield 'message' => ['item/completed', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'item' => ['type' => 'agentMessage', 'id' => 'message-1', 'text' => 'Done', 'phase' => 'final', 'questions' => [['id' => 'question-1', 'question' => 'Proceed?']]]]];
        yield 'reasoning' => ['item/completed', ['item' => ['type' => 'reasoning', 'id' => 'reasoning-1', 'summary' => ['One', 'Two'], 'content' => ['Detail']]]];
        yield 'experimental plan' => ['item/completed', ['item' => ['type' => 'plan', 'id' => 'plan-1', 'text' => 'Review the tests']]];
        yield 'unknown item' => ['item/completed', ['item' => ['type' => 'future', 'id' => 'future-1', 'payload' => true]]];
    }

    #[DataProvider('unsuccessfulTurns')]
    public function testUnsuccessfulCompletionRetainsStatusAndError(string $status, ?array $error, string $message): void
    {
        $raw = $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
            'id' => 'turn-1', 'status' => $status, 'items' => [], 'error' => $error,
        ]]);

        $event = $this->parse($raw)[0];

        self::assertInstanceOf(TurnFailed::class, $event);
        self::assertSame($status, $event->status);
        self::assertSame($message, $event->message);
        self::assertSame($raw, $event->raw());
    }

    public static function unsuccessfulTurns(): iterable
    {
        yield 'interrupted without error' => ['interrupted', null, 'The turn was interrupted.'];
        yield 'strict circuit breaker' => ['interrupted', ['message' => 'Too many denials', 'codexErrorInfo' => 'tooManyDenials', 'additionalDetails' => null], 'Too many denials'];
        yield 'failed without error' => ['failed', null, 'The turn failed.'];
        yield 'failed with error' => ['failed', ['message' => 'Unavailable', 'codexErrorInfo' => 'flexUnavailable'], 'Unavailable'];
    }

    /** @param array<string, mixed> $params @return array<string, mixed> */
    private function notification(string $method, array $params): array
    {
        return ['method' => $method, 'params' => $params, 'futureEnvelopeField' => 'retained'];
    }

    /** @param array<string, mixed> $raw @return list<CodexEvent> */
    private function parse(array $raw): array
    {
        return (new JsonlEventParser())->parse((new NotificationTranslator())->translate(
            new Notification($raw['method'], $raw['params'], $raw),
        ));
    }
}
