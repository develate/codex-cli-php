<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CollaborationToolCall;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\ModelRerouted;
use Develate\CodexCli\Event\ThreadStarted;
use Develate\CodexCli\Event\ThreadTokenUsageUpdated;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Event\TurnFailed;
use Develate\CodexCli\Event\TurnStarted;
use Develate\CodexCli\Event\UnknownEvent;
use Develate\CodexCli\Exception\AppServerException;
use Develate\CodexCli\Exception\UnsupportedApproval;
use Develate\CodexCli\Thread;
use Develate\CodexCli\Transport\ApprovalRequest;
use Develate\CodexCli\Transport\AppServerTurnTransport;
use Develate\CodexCli\Transport\ElicitationResponse;
use Develate\CodexCli\Transport\PermissionRequest;
use Develate\CodexCli\Transport\PermissionResponse;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Value\ApprovalDecision;
use Develate\CodexCli\Value\ElicitationAction;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The interactive transport, against a fake app server that speaks the real
 * protocol both ways.
 */
final class AppServerTurnTransportTest extends TestCase
{
    /** @var list<string> */
    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function testStreamsATurnAsTheSameEventsTheExecTransportProduces(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/started', ['item' => ['type' => 'agentMessage', 'id' => 'i1', 'text' => 'Working']]),
            $this->notification('item/completed', ['item' => [
                'type' => 'commandExecution', 'id' => 'c1', 'command' => 'ls',
                'status' => 'completed', 'aggregatedOutput' => 'README.md', 'exitCode' => 0,
            ]]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
                'id' => 'turn-1', 'status' => 'completed', 'items' => [],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 3],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());

        self::assertInstanceOf(ThreadStarted::class, $events[0]);
        self::assertSame('thread-1', $events[0]->threadId);
        self::assertInstanceOf(TurnStarted::class, $events[1]);
        self::assertSame('thread-1', $events[1]->threadId);
        self::assertSame('turn-1', $events[1]->turnId);
        self::assertInstanceOf(AgentMessage::class, $events[2]);
        self::assertSame('Working', $events[2]->text);
        self::assertInstanceOf(CommandExecution::class, $events[3]);
        self::assertSame('ls', $events[3]->command);
        self::assertSame('README.md', $events[3]->output);
        self::assertInstanceOf(TurnCompleted::class, $events[4]);
        self::assertSame('thread-1', $events[4]->threadId);
        self::assertSame('turn-1', $events[4]->turnId);
        self::assertSame(10, $events[4]->usage->inputTokens);
    }

    public function testStreamsCollaborationIdentityAndEveryTokenUsageSnapshot(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/started', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'item' => [
                    'type' => 'collabToolCall',
                    'id' => 'spawn-one',
                    'senderThreadId' => 'thread-1',
                    'receiverThreadId' => 'child-one',
                    'model' => 'gpt-5.6-luna',
                    'status' => 'inProgress',
                    'tool' => 'spawnAgent',
                ],
            ]),
            $this->notification('item/completed', [
                'threadId' => 'child-one',
                'turnId' => 'child-turn',
                'item' => [
                    'type' => 'collabAgentToolCall',
                    'id' => 'spawn-two',
                    'senderThreadId' => 'child-one',
                    'receiverThreadIds' => ['child-two'],
                    'model' => 'gpt-6-astra',
                    'status' => 'completed',
                    'tool' => 'spawnAgent',
                ],
            ]),
            $this->notification('thread/tokenUsage/updated', [
                'threadId' => 'child-one',
                'turnId' => 'child-turn',
                'tokenUsage' => [
                    'last' => ['inputTokens' => 11, 'cachedInputTokens' => 2, 'outputTokens' => 3, 'reasoningOutputTokens' => 1],
                    'total' => ['inputTokens' => 41, 'cachedInputTokens' => 5, 'outputTokens' => 8, 'reasoningOutputTokens' => 2],
                ],
            ]),
            $this->notification('thread/tokenUsage/updated', [
                'threadId' => 'child-one',
                'turnId' => 'child-turn',
                'tokenUsage' => [
                    'last' => ['inputTokens' => 11, 'cachedInputTokens' => 2, 'outputTokens' => 3, 'reasoningOutputTokens' => 1],
                    'total' => ['inputTokens' => 41, 'cachedInputTokens' => 5, 'outputTokens' => 8, 'reasoningOutputTokens' => 2],
                ],
            ]),
            $this->notification('model/rerouted', [
                'threadId' => 'child-two',
                'turnId' => 'nested-turn',
                'fromModel' => 'gpt-6-astra',
                'toModel' => 'gpt-5.6-sol',
                'reason' => 'highRiskCyberActivity',
            ]),
            $this->notification('item/updated', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'item' => ['type' => 'futureItem', 'id' => 'future-1'],
            ]),
            $this->notification('thread/future', ['threadId' => 'thread-1']),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
                'id' => 'turn-1', 'status' => 'completed', 'items' => [],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());
        $collaboration = array_values(array_filter(
            $events,
            static fn (CodexEvent $event): bool => $event instanceof CollaborationToolCall,
        ));
        $usage = array_values(array_filter(
            $events,
            static fn (CodexEvent $event): bool => $event instanceof ThreadTokenUsageUpdated,
        ));
        $unknown = array_values(array_filter(
            $events,
            static fn (CodexEvent $event): bool => $event instanceof UnknownEvent,
        ));

        self::assertCount(2, $collaboration);
        self::assertSame('thread-1', $collaboration[0]->senderThreadId);
        self::assertSame(['child-one'], $collaboration[0]->receiverThreadIds);
        self::assertSame('gpt-5.6-luna', $collaboration[0]->requestedModel);
        self::assertSame('inProgress', $collaboration[0]->status);
        self::assertSame('child-one', $collaboration[1]->senderThreadId);
        self::assertSame(['child-two'], $collaboration[1]->receiverThreadIds);
        self::assertSame('gpt-6-astra', $collaboration[1]->requestedModel);
        self::assertSame('item/started', $collaboration[0]->raw()['method']);

        self::assertCount(2, $usage, 'Repeated app-server snapshots remain observable to callers.');
        self::assertSame('child-one', $usage[0]->threadId);
        self::assertSame('child-turn', $usage[0]->turnId);
        self::assertSame(11, $usage[0]->last->inputTokens);
        self::assertSame(41, $usage[0]->total->inputTokens);
        self::assertSame('thread/tokenUsage/updated', $usage[0]->raw()['method']);
        self::assertSame(11, $usage[1]->last->inputTokens);
        self::assertSame(41, $usage[1]->total->inputTokens);

        $reroute = current(array_filter($events, static fn (CodexEvent $event): bool => $event instanceof ModelRerouted));
        self::assertInstanceOf(ModelRerouted::class, $reroute);
        self::assertSame('child-two', $reroute->threadId);
        self::assertSame('nested-turn', $reroute->turnId);
        self::assertSame('gpt-6-astra', $reroute->fromModel);
        self::assertSame('gpt-5.6-sol', $reroute->toModel);

        self::assertSame(['item/updated', 'thread/future'], array_map(
            static fn (UnknownEvent $event): string => $event->type,
            $unknown,
        ));
        self::assertSame('futureItem', $unknown[0]->raw()['params']['item']['type']);
    }

    public function testASubAgentFinishingDoesNotEndOrSpeakForTheRootTurn(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/completed', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'item' => [
                    'type' => 'collabAgentToolCall', 'id' => 'spawn', 'senderThreadId' => 'thread-1',
                    'receiverThreadId' => 'child', 'status' => 'completed', 'tool' => 'spawnAgent',
                ],
            ]),
            $this->notification('thread/started', ['thread' => ['id' => 'child']]),
            $this->notification('turn/started', ['threadId' => 'child', 'turn' => ['id' => 'child-turn']]),
            $this->notification('item/completed', ['threadId' => 'child', 'turnId' => 'child-turn', 'item' => [
                'type' => 'agentMessage', 'id' => 'child-message', 'text' => 'Handed the notes to the main agent.',
            ]]),
            $this->notification('thread/tokenUsage/updated', [
                'threadId' => 'child',
                'turnId' => 'child-turn',
                'tokenUsage' => ['last' => ['inputTokens' => 5], 'total' => ['inputTokens' => 5]],
            ]),
            $this->notification('turn/completed', ['threadId' => 'child', 'turn' => [
                'id' => 'child-turn', 'status' => 'completed', 'items' => [],
            ]]),
            $this->notification('item/completed', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'item' => [
                'type' => 'agentMessage', 'id' => 'root-message', 'text' => 'Done.',
            ]]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
                'id' => 'turn-1', 'status' => 'completed', 'items' => [],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());
        $of = static fn (string $class): array => array_values(array_filter(
            $events,
            static fn (CodexEvent $event): bool => $event instanceof $class,
        ));

        self::assertSame(['Done.'], array_map(static fn (AgentMessage $message): string => $message->text, $of(AgentMessage::class)));
        self::assertSame(['thread-1'], array_map(static fn (ThreadStarted $event): string => $event->threadId, $of(ThreadStarted::class)));
        self::assertCount(1, $of(TurnStarted::class));
        self::assertCount(1, $of(CollaborationToolCall::class));
        self::assertCount(1, $of(ThreadTokenUsageUpdated::class), 'Sub-agent usage stays observable.');

        $completed = $of(TurnCompleted::class);
        self::assertCount(1, $completed);
        self::assertSame('turn-1', $completed[0]->turnId);
        self::assertNotContains('turn/interrupt', array_column($this->requests($binary), 'method'));
    }

    public function testTurnUsageComesFromRootTokenSnapshotsWithoutCountingRepeatsOrPriorTurns(): void
    {
        $later = $this->notification('thread/tokenUsage/updated', [
            'threadId' => 'thread-1', 'turnId' => 'turn-1',
            'tokenUsage' => [
                'last' => ['inputTokens' => 20, 'cachedInputTokens' => 3, 'cacheWriteInputTokens' => 2, 'outputTokens' => 7, 'reasoningOutputTokens' => 3],
                'total' => ['inputTokens' => 130, 'cachedInputTokens' => 8, 'cacheWriteInputTokens' => 5, 'outputTokens' => 17, 'reasoningOutputTokens' => 9],
            ],
        ]);
        $earlier = $this->notification('thread/tokenUsage/updated', [
            'threadId' => 'thread-1', 'turnId' => 'turn-1',
            'tokenUsage' => [
                'last' => ['inputTokens' => 10, 'cachedInputTokens' => 1, 'cacheWriteInputTokens' => 1, 'outputTokens' => 2, 'reasoningOutputTokens' => 1],
                'total' => ['inputTokens' => 110, 'cachedInputTokens' => 5, 'cacheWriteInputTokens' => 3, 'outputTokens' => 10, 'reasoningOutputTokens' => 6],
            ],
        ]);
        $binary = $this->fakeAppServer([
            $later,
            $later,
            $this->notification('thread/tokenUsage/updated', [
                'threadId' => 'child-thread', 'turnId' => 'child-turn',
                'tokenUsage' => [
                    'last' => ['inputTokens' => 500, 'outputTokens' => 200],
                    'total' => ['inputTokens' => 500, 'outputTokens' => 200],
                ],
            ]),
            $earlier,
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
                'id' => 'turn-1', 'status' => 'completed', 'items' => [],
            ]]),
        ]);
        $completed = null;
        $thread = new Thread(new AppServerTurnTransport($binary), '0.157.1', sys_get_temp_dir(), null, Sandbox::WorkspaceWrite);
        $thread->onEvent(static function (CodexEvent $event) use (&$completed): void {
            if ($event instanceof TurnCompleted) {
                $completed = $event;
            }
        });

        $result = $thread->run('Do the thing');

        self::assertSame(30, $result->usage->inputTokens);
        self::assertSame(4, $result->usage->cachedInputTokens);
        self::assertSame(3, $result->usage->cacheWriteInputTokens);
        self::assertSame(9, $result->usage->outputTokens);
        self::assertSame(4, $result->usage->reasoningOutputTokens);
        self::assertInstanceOf(TurnCompleted::class, $completed);
        self::assertSame(30, $completed->usage->inputTokens);
        self::assertSame(9, $completed->usage->outputTokens);
    }

    public function testACommandApprovalIsAnsweredWhileTheAgentWaits(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/commandExecution/requestApproval', [
                'itemId' => 'c1',
                'command' => 'rm -rf build',
                'cwd' => '/tmp/project',
            ]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $asked = [];
        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(function (ApprovalRequest $request) use (&$asked): ApprovalDecision {
            $asked[] = $request;

            return ApprovalDecision::Accept;
        });

        $this->collect($transport, $this->request());

        self::assertCount(1, $asked);
        self::assertSame(ApprovalRequest::KIND_COMMAND, $asked[0]->kind);
        self::assertSame('rm -rf build', $asked[0]->subject, 'The command must reach the user in full.');
        self::assertSame('accept', $this->decisions($binary)[0]);
    }

    public function testADeniedApprovalIsSentBackAsADecline(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/fileChange/requestApproval', ['itemId' => 'f1', 'reason' => 'write outside workspace']),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(fn (ApprovalRequest $request): ApprovalDecision => ApprovalDecision::Decline);

        $this->collect($transport, $this->request());

        self::assertSame('decline', $this->decisions($binary)[0]);
    }

    public function testARequestForAdditionalPermissionsReceivesTheGrantedProfile(): void
    {
        $permissions = [
            'network' => ['enabled' => true],
            'fileSystem' => ['write' => ['/tmp/shared']],
        ];
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/permissions/requestApproval', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'itemId' => 'permissions-1',
                'environmentId' => 'local',
                'startedAtMs' => 1_757_500_000_000,
                'cwd' => '/tmp/project',
                'reason' => 'Select a workspace root',
                'permissions' => $permissions,
            ]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $asked = [];
        $transport = new AppServerTurnTransport($binary);
        $transport->onPermissionRequest(function (PermissionRequest $request) use (&$asked): PermissionResponse {
            $asked[] = $request;

            return PermissionResponse::grant($request->permissions);
        });

        $this->collect($transport, $this->request());

        self::assertCount(1, $asked);
        self::assertSame('permissions-1', $asked[0]->itemId);
        self::assertSame('/tmp/project', $asked[0]->cwd);
        self::assertSame('Select a workspace root', $asked[0]->reason);
        self::assertSame($permissions, $asked[0]->permissions);
        self::assertSame(
            ['permissions' => $permissions],
            $this->permissionResults($binary)[0],
        );
    }

    public function testADeniedPermissionRequestReceivesAnEmptyProfileAndTheTurnContinues(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/permissions/requestApproval', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'itemId' => 'permissions-1',
                'startedAtMs' => 1_757_500_000_000,
                'cwd' => '/tmp/project',
                'permissions' => ['network' => ['enabled' => true]],
            ]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $transport = new AppServerTurnTransport($binary);
        $transport->onPermissionRequest(fn (): PermissionResponse => PermissionResponse::deny());

        $events = $this->collect($transport, $this->request());

        self::assertInstanceOf(TurnCompleted::class, end($events));
        self::assertSame(['permissions' => []], $this->permissionResults($binary)[0]);
    }

    public function testAnAdditionalPermissionRequestWithoutAHandlerFailsVisibly(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/permissions/requestApproval', [
                'threadId' => 'thread-1',
                'turnId' => 'turn-1',
                'itemId' => 'permissions-1',
                'startedAtMs' => 1_757_500_000_000,
                'cwd' => '/tmp/project',
                'permissions' => ['network' => ['enabled' => true]],
            ]),
        ]);

        $this->expectException(UnsupportedApproval::class);

        $this->collect(new AppServerTurnTransport($binary), $this->request());
    }

    public function testAnApprovalNobodyCanAnswerFailsTheTurnRatherThanGrantingAccess(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/commandExecution/requestApproval', ['itemId' => 'c1', 'command' => 'curl evil.example']),
        ]);

        // No handler registered: there is nobody to ask.
        $this->expectException(UnsupportedApproval::class);

        $this->collect(new AppServerTurnTransport($binary), $this->request());
    }

    public function testAnApprovalKindThisClientDoesNotKnowFailsVisibly(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/somethingNew/requestApproval', ['itemId' => 'x1']),
        ]);

        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(fn (ApprovalRequest $request): ApprovalDecision => ApprovalDecision::Accept);

        $this->expectException(UnsupportedApproval::class);

        $this->collect($transport, $this->request());
    }

    public function testElicitationReturnsStructuredContentAndTheTurnContinues(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('mcpServer/elicitation/request', [
                'mode' => 'form', 'serverName' => 'boost', 'message' => 'Choose settings',
                'threadId' => 'thread-1', 'turnId' => 'turn-1',
                'requestedSchema' => ['type' => 'object', 'properties' => [
                    'confirm' => ['type' => 'boolean'], 'count' => ['type' => 'integer'],
                ], 'required' => ['confirm', 'count']],
            ]),
            $this->notification('turn/completed', ['turn' => ['id' => 'turn-1', 'status' => 'completed']]),
        ]);
        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(static function (): never {
            self::fail('Elicitation is not an approval.');
        });
        $transport->onElicitation(function ($request) {
            self::assertSame('boost', $request->serverName);
            self::assertSame('Choose settings', $request->message);
            self::assertSame('thread-1', $request->threadId);

            return new ElicitationResponse(
                ElicitationAction::Accept, ['confirm' => false, 'count' => 0],
            );
        });

        $events = $this->collect($transport, $this->request());

        self::assertInstanceOf(TurnCompleted::class, end($events));
        self::assertSame(['action' => 'accept', 'content' => ['confirm' => false, 'count' => 0]], $this->elicitationResults($binary)[0]);
    }

    public function testUnhandledAndUnsupportedElicitationsCancelWithoutFailingTheTurn(): void
    {
        foreach ([
            ['mode' => 'form', 'requestedSchema' => ['type' => 'object', 'properties' => []]],
            ['mode' => 'openai/form', 'requestedSchema' => ['type' => 'object']],
            ['mode' => 'form', 'requestedSchema' => ['type' => 'object', 'properties' => ['nested' => ['type' => 'object']]]],
            ['mode' => 'url', 'url' => 'javascript:alert(1)'],
        ] as $params) {
            $binary = $this->fakeAppServer([
                $this->serverRequest('mcpServer/elicitation/request', $params),
                $this->notification('turn/completed', ['turn' => ['id' => 'turn-1', 'status' => 'completed']]),
            ]);
            $events = $this->collect(new AppServerTurnTransport($binary), $this->request());
            self::assertInstanceOf(TurnCompleted::class, end($events));
            self::assertSame([['action' => 'cancel']], $this->elicitationResults($binary));
        }
    }

    public function testUrlElicitationsKeepAcceptDeclineAndCancelDistinctAndDiscardNonAcceptedContent(): void
    {
        foreach (ElicitationAction::cases() as $action) {
            $binary = $this->fakeAppServer([
                $this->serverRequest('mcpServer/elicitation/request', [
                    'mode' => 'url', 'url' => 'https://example.com/login', 'elicitationId' => 'login-1',
                ]),
                $this->notification('turn/completed', ['turn' => ['id' => 'turn-1', 'status' => 'completed']]),
            ]);
            $transport = new AppServerTurnTransport($binary);
            $transport->onElicitation(function ($request) use ($action) {
                self::assertSame('login-1', $request->elicitationId);
                self::assertSame('https://example.com/login', $request->url);

                return new ElicitationResponse($action);
            });
            $this->collect($transport, $this->request());
            self::assertSame([['action' => $action->value]], $this->elicitationResults($binary));
        }
    }

    /** @return list<array<string, mixed>> */
    private function elicitationResults(string $binary): array
    {
        return array_values(array_map(
            static fn (array $message): array => $message['result'],
            array_filter($this->requests($binary), static fn (array $message): bool => isset($message['result']['action'])),
        ));
    }

    public function testAFailedTurnIsReportedAsAFailure(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
                'id' => 'turn-1', 'status' => 'failed', 'items' => [],
                'error' => ['message' => 'The model refused.'],
            ]]),
        ]);

        $events = $this->collect(new AppServerTurnTransport($binary), $this->request());
        $failure = end($events);

        self::assertInstanceOf(TurnFailed::class, $failure);
        self::assertSame('The model refused.', $failure->message);
    }

    public function testResumingSendsTheThreadIdRatherThanStartingANewThread(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $this->collect(new AppServerTurnTransport($binary), $this->request(mode: RunMode::Resume, threadId: 'existing-thread'));

        $methods = array_column($this->requests($binary), 'method');

        self::assertContains('thread/resume', $methods);
        self::assertNotContains('thread/start', $methods);
    }

    public function testForkingUsesTheParentIdAndResumesTheReturnedChild(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('turn/completed', ['threadId' => 'child-thread', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ], [
            'thread/start' => ['thread' => ['id' => 'child-thread']],
            'thread/fork' => ['thread' => ['id' => 'child-thread', 'forkedFromId' => 'parent-thread']],
            'thread/resume' => ['thread' => ['id' => 'child-thread']],
        ]);
        $parent = new Thread(new AppServerTurnTransport($binary), '0.160.0', sys_get_temp_dir(), 'gpt-test', Sandbox::WorkspaceWrite, threadId: 'parent-thread');
        $child = $parent->fork();
        $first = $child->run('Try an alternative');
        $second = $child->run('Continue the alternative');
        $requests = $this->requests($binary);
        $forks = array_values(array_filter($requests, static fn (array $message): bool => ($message['method'] ?? null) === 'thread/fork'));
        $resumes = array_values(array_filter($requests, static fn (array $message): bool => ($message['method'] ?? null) === 'thread/resume'));

        self::assertCount(1, $forks);
        self::assertSame('parent-thread', $forks[0]['params']['threadId']);
        self::assertTrue($forks[0]['params']['excludeTurns']);
        self::assertSame('gpt-test', $forks[0]['params']['model']);
        self::assertSame('workspace-write', $forks[0]['params']['sandbox']);
        self::assertCount(1, $resumes);
        self::assertSame('child-thread', $resumes[0]['params']['threadId']);
        self::assertSame('parent-thread', $parent->id());
        self::assertSame('child-thread', $first->threadId);
        self::assertSame('child-thread', $second->threadId);
        self::assertNotContains('thread/start', array_column($requests, 'method'));
    }

    public function testInterruptedTurnsRemainObservableAsErrorsInTheResult(): void
    {
        $completion = $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => [
            'id' => 'turn-1', 'status' => 'interrupted', 'items' => [], 'error' => null,
        ]]);
        $binary = $this->fakeAppServer([$completion]);
        $thread = new Thread(new AppServerTurnTransport($binary), '0.160.0', sys_get_temp_dir(), null, Sandbox::ReadOnly);

        $result = $thread->run('Do the thing');

        self::assertCount(1, $result->errors());
        self::assertInstanceOf(TurnFailed::class, $result->errors()[0]);
        self::assertSame('interrupted', $result->errors()[0]->status);
        self::assertSame($completion, $result->errors()[0]->raw());
        self::assertNotContains('turn/interrupt', array_column($this->requests($binary), 'method'));
    }

    public function testCancellingAnApprovalReportsTheInterruptedTurn(): void
    {
        $binary = $this->fakeAppServer([
            $this->serverRequest('item/commandExecution/requestApproval', ['threadId' => 'thread-1', 'turnId' => 'turn-1', 'itemId' => 'command-1', 'command' => 'pwd']),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'interrupted', 'items' => [], 'error' => null]]),
        ]);
        $transport = new AppServerTurnTransport($binary);
        $transport->onApproval(static fn (ApprovalRequest $request): ApprovalDecision => ApprovalDecision::Cancel);
        $thread = new Thread($transport, '0.160.0', sys_get_temp_dir(), null, Sandbox::ReadOnly);

        $result = $thread->run('Do the thing');

        self::assertSame(['cancel'], $this->decisions($binary));
        self::assertCount(1, $result->errors());
        self::assertSame('interrupted', $result->errors()[0]->status);
        self::assertNotContains('turn/interrupt', array_column($this->requests($binary), 'method'));
    }

    #[DataProvider('invalidForkResponses')]
    public function testForkRejectsAnInvalidChildId(array $response): void
    {
        $binary = $this->fakeAppServer([], ['thread/fork' => $response]);

        $this->expectException(AppServerException::class);
        $this->expectExceptionMessage('without a new thread ID');
        $this->collect(new AppServerTurnTransport($binary), $this->request(RunMode::Fork, 'parent-thread'));
    }

    public static function invalidForkResponses(): iterable
    {
        yield 'missing id' => [[]];
        yield 'empty id' => [['thread' => ['id' => '']]];
        yield 'parent id reused' => [['thread' => ['id' => 'parent-thread']]];
    }

    #[DataProvider('missingThreadIds')]
    public function testForkRequiresAParentBeforeStartingTheServer(?string $threadId): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('require a thread id');
        $this->collect(new AppServerTurnTransport('/does-not-exist/codex'), $this->request(RunMode::Fork, $threadId));
    }

    public static function missingThreadIds(): iterable
    {
        yield 'null' => [null];
        yield 'empty' => [''];
    }

    public function testAbandoningTheStreamInterruptsTheTurn(): void
    {
        $binary = $this->fakeAppServer([
            $this->notification('item/started', ['item' => ['type' => 'agentMessage', 'id' => 'i1', 'text' => 'Thinking']]),
            $this->notification('turn/completed', ['threadId' => 'thread-1', 'turn' => ['id' => 'turn-1', 'status' => 'completed', 'items' => []]]),
        ]);

        $stream = (new AppServerTurnTransport($binary))->stream($this->request());

        foreach ($stream as $event) {
            if ($event instanceof AgentMessage) {
                break;
            }
        }

        unset($stream);

        self::assertContains('turn/interrupt', array_column($this->requests($binary), 'method'));
    }

    private function request(RunMode $mode = RunMode::Start, ?string $threadId = null): RunRequest
    {
        return new RunRequest(
            mode: $mode,
            threadId: $threadId,
            prompt: 'Do the thing',
            cwd: sys_get_temp_dir(),
            model: 'gpt-5-codex',
            sandbox: Sandbox::WorkspaceWrite,
            timeout: 10.0,
        );
    }

    /**
     * @return list<CodexEvent>
     */
    private function collect(AppServerTurnTransport $transport, RunRequest $request): array
    {
        $events = [];

        foreach ($transport->stream($request) as $event) {
            $events[] = $event;
        }

        return $events;
    }

    /** @return array<string, mixed> */
    private function notification(string $method, array $params): array
    {
        return ['method' => $method, 'params' => $params];
    }

    /** @return array<string, mixed> */
    private function serverRequest(string $method, array $params): array
    {
        return ['method' => $method, 'params' => $params, 'expectsResponse' => true];
    }

    /** @return list<array<string, mixed>> */
    private function requests(string $binary): array
    {
        $log = dirname($binary).DIRECTORY_SEPARATOR.'requests.jsonl';

        if (! is_file($log)) {
            return [];
        }

        return array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            array_filter(file($log, FILE_IGNORE_NEW_LINES)),
        );
    }

    /** @return list<string> */
    private function decisions(string $binary): array
    {
        $decisions = [];

        foreach ($this->requests($binary) as $message) {
            if (isset($message['result']['decision'])) {
                $decisions[] = $message['result']['decision'];
            }
        }

        return $decisions;
    }

    /** @return list<array<string, mixed>> */
    private function permissionResults(string $binary): array
    {
        $results = [];

        foreach ($this->requests($binary) as $message) {
            if (isset($message['result']['permissions'])) {
                $results[] = $message['result'];
            }
        }

        return $results;
    }

    /**
     * A fake `codex app-server` that answers the calls this transport makes and
     * then plays a scripted sequence of notifications and server requests.
     *
     * @param  list<array<string, mixed>>  $script
     * @param  array<string, array<string, mixed>>  $responses
     */
    private function fakeAppServer(array $script, array $responses = []): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'codex-turn-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $server = $directory.DIRECTORY_SEPARATOR.'app-server.php';
        $binary = $directory.DIRECTORY_SEPARATOR.'codex';
        $log = $directory.DIRECTORY_SEPARATOR.'requests.jsonl';

        $body = strtr(<<<'PHP'
        <?php
        $script = json_decode(__SCRIPT__, true);
        $responses = json_decode(__RESPONSES__, true);
        $log = __LOG__;
        $nextId = 9000;
        $emit = static function (array $message) use ($log): void {
            fwrite(STDOUT, json_encode($message)."\n");
            fflush(STDOUT);
        };
        $play = static function () use (&$script, &$nextId, $emit): void {
            while ($script !== []) {
                $step = array_shift($script);
                if (($step['expectsResponse'] ?? false) === true) {
                    $emit(['id' => $nextId++, 'method' => $step['method'], 'params' => $step['params']]);
                    return;
                }
                $emit(['method' => $step['method'], 'params' => $step['params']]);
            }
        };
        while (($line = fgets(STDIN)) !== false) {
            $message = json_decode(trim($line), true);
            if (! is_array($message)) {
                continue;
            }
            file_put_contents($log, json_encode($message)."\n", FILE_APPEND);
            if (! isset($message['method'])) {
                // A response to one of our own server requests: carry on.
                $play();
                continue;
            }
            if (! isset($message['id'])) {
                continue;
            }
            $result = $responses[$message['method']] ?? match ($message['method']) {
                'initialize' => ['userAgent' => 'fake'],
                'thread/start', 'thread/resume' => ['thread' => ['id' => 'thread-1']],
                'turn/start' => ['turn' => ['id' => 'turn-1', 'status' => 'inProgress', 'items' => []]],
                default => [],
            };
            $emit(['id' => $message['id'], 'result' => $result]);
            if ($message['method'] === 'turn/start') {
                $play();
            }
        }
        PHP, [
            '__SCRIPT__' => var_export(json_encode($script, JSON_THROW_ON_ERROR), true),
            '__RESPONSES__' => var_export(json_encode($responses, JSON_THROW_ON_ERROR), true),
            '__LOG__' => var_export($log, true),
        ]);

        file_put_contents($server, $body);
        file_put_contents($binary, sprintf("#!/bin/sh\nexec %s %s \"\$@\"\n", escapeshellarg(PHP_BINARY), escapeshellarg($server)));
        chmod($binary, 0700);

        return $binary;
    }
}
