<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\AppServer\AppServerClient;
use Develate\CodexCli\AppServer\JsonRpcConnection;
use Develate\CodexCli\AppServer\Notification;
use Develate\CodexCli\AppServer\NotificationTranslator;
use Develate\CodexCli\AppServer\ServerRequest;
use Develate\CodexCli\Event\CodexEvent;
use Develate\CodexCli\Exception\AppServerException;
use Develate\CodexCli\Exception\UnsupportedApproval;
use Develate\CodexCli\Parser\JsonlEventParser;
use Develate\CodexCli\Process\ProcessResult;
use Develate\CodexCli\Value\ApprovalDecision;
use Develate\CodexCli\Value\ElicitationAction;
use Develate\CodexCli\Value\Sandbox;

/**
 * Runs a turn over the app server's bidirectional connection.
 *
 * The difference that matters is not speed but direction: `codex exec` can only
 * talk, so anything it wants to do it either does or refuses on its own. Over
 * this transport the agent can stop mid-turn and ask, and the answer travels
 * back on the same connection while it waits.
 *
 * Common activity arrives as the same `CodexEvent` objects as the exec
 * transport. App-server-only protocol facts, such as collaboration identity
 * and token snapshots, remain typed instead of being discarded.
 *
 * @see https://developers.openai.com/codex/app-server
 */
final class AppServerTurnTransport implements Transport
{
    /** @var (callable(ApprovalRequest): ApprovalDecision)|null */
    private $approvalHandler = null;

    /** @var (callable(ElicitationRequest): ElicitationResponse)|null */
    private $elicitationHandler = null;

    /** @var (callable(PermissionRequest): PermissionResponse)|null */
    private $permissionHandler = null;

    private ?AppServerClient $client = null;

    private ?JsonRpcConnection $connection = null;

    private ?string $threadId = null;

    private ?string $turnId = null;

    private bool $turnFinished = false;

    private bool $turnStartedEventEmitted = false;

    /** @param array<string, string> $configOverrides */
    public function __construct(
        private readonly string $binary = 'codex',
        private readonly float $requestTimeout = 30.0,
        private readonly NotificationTranslator $translator = new NotificationTranslator,
        private readonly JsonlEventParser $parser = new JsonlEventParser,
        private readonly array $configOverrides = [],
    ) {}

    /**
     * How approvals are answered.
     *
     * Without a handler the agent may still run, but any approval it asks for
     * fails the turn rather than being decided for the user.
     *
     * @param  callable(ApprovalRequest): ApprovalDecision  $handler
     */
    public function onApproval(callable $handler): void
    {
        $this->approvalHandler = $handler;
    }

    /** @param callable(ElicitationRequest): ElicitationResponse $handler */
    public function onElicitation(callable $handler): void
    {
        $this->elicitationHandler = $handler;
    }

    /** @param callable(PermissionRequest): PermissionResponse $handler */
    public function onPermissionRequest(callable $handler): void
    {
        $this->permissionHandler = $handler;
    }

    /**
     * The thread the last run used, so the caller can resume it next time.
     */
    public function threadId(): ?string
    {
        return $this->threadId;
    }

    /**
     * @return \Generator<int, CodexEvent, void, ProcessResult>
     */
    public function stream(RunRequest $request): \Generator
    {
        $this->connection = new JsonRpcConnection($this->binary, $this->requestTimeout, $this->configOverrides);
        $this->client = new AppServerClient($this->connection);
        $this->client->onServerRequest(fn (ServerRequest $serverRequest): array => $this->answer($serverRequest));

        $deadline = microtime(true) + ($request->timeout ?? 3600.0);
        $this->turnFinished = false;
        $this->turnStartedEventEmitted = false;

        try {
            $this->threadId = $request->threadId !== null && $request->mode === RunMode::Resume
                ? $this->client->threadResume($request->threadId, $this->threadParams($request))
                : $this->client->threadStart($this->threadParams($request));

            // Emitted here rather than waited for: the caller stores this ID to
            // resume the thread later, and a `thread/started` notification only
            // arrives for a genuinely new thread.
            yield from $this->events(['type' => 'thread.started', 'thread_id' => $this->threadId]);

            $this->turnId = $this->client->turnStart($this->turnParams($request));
            $this->turnStartedEventEmitted = true;

            // `turn/start` already returns these lifecycle identifiers. Emit
            // them even when a server version does not repeat turn/started as
            // a notification, then suppress that duplicate below.
            yield from $this->events([
                'type' => 'turn.started',
                'thread_id' => $this->threadId,
                'turn_id' => $this->turnId,
            ]);

            yield from $this->drain();

            while (microtime(true) < $deadline) {
                if (! $this->client->pump($this->slice($deadline))) {
                    continue;
                }

                $finished = false;

                foreach ($this->client->drain() as $notification) {
                    $translated = $this->translator->translate($notification);

                    if ($translated['type'] === 'turn.started' && $this->turnStartedEventEmitted) {
                        continue;
                    }

                    yield from $this->events($translated);

                    if (in_array($translated['type'], ['turn.completed', 'turn.failed'], true)) {
                        $finished = true;
                    }
                }

                if ($finished) {
                    $this->turnFinished = true;

                    return new ProcessResult(0, '');
                }
            }

            throw new AppServerException('The Codex turn did not finish within its timeout.');
        } finally {
            // Reached both when the turn ends and when the caller abandons the
            // generator, which is how a cancellation gets to the agent.
            $this->stop();
        }
    }

    /**
     * @param  array<string, mixed>  $event
     * @return \Generator<int, CodexEvent, void, void>
     */
    private function events(array $event): \Generator
    {
        foreach ($this->parser->parse($event) as $parsed) {
            yield $parsed;
        }
    }

    /**
     * @return \Generator<int, CodexEvent, void, void>
     */
    private function drain(): \Generator
    {
        foreach ($this->client?->drain() ?? [] as $notification) {
            $translated = $this->translator->translate($notification);

            if ($translated['type'] === 'turn.started' && $this->turnStartedEventEmitted) {
                continue;
            }

            yield from $this->events($translated);
        }
    }

    /**
     * Answers a request from the server.
     *
     * @return array<string, mixed>
     */
    private function answer(ServerRequest $request): array
    {
        if ($request->method === 'mcpServer/elicitation/request') {
            $elicitation = ElicitationRequest::fromParams($request->params);

            if ($elicitation === null || $this->elicitationHandler === null) {
                return ['action' => 'cancel'];
            }

            $response = ($this->elicitationHandler)($elicitation);

            if ($response->action === ElicitationAction::Accept
                && $elicitation->mode === ElicitationRequest::MODE_FORM) {
                ElicitationSchema::validate($elicitation->schema, $response->content ?? []);
            }

            return $response->toArray();
        }

        if ($request->method === PermissionRequest::METHOD) {
            $permission = PermissionRequest::fromParams($request->params);

            if ($permission === null || $this->permissionHandler === null) {
                throw new UnsupportedApproval($request->method);
            }

            return ($this->permissionHandler)($permission)->toArray();
        }

        $approval = $this->approvalRequest($request);

        // Unknown approval methods fail the turn instead of being answered.
        // A default of "yes" would grant access nobody looked at, and a default
        // of "no" would be recorded as a decision the user never made.
        if ($approval === null) {
            throw new UnsupportedApproval($request->method);
        }

        if ($this->approvalHandler === null) {
            throw new UnsupportedApproval($request->method);
        }

        return ['decision' => ($this->approvalHandler)($approval)->value];
    }

    private function approvalRequest(ServerRequest $request): ?ApprovalRequest
    {
        $params = $request->params;

        return match ($request->method) {
            // The current names, and the ones older servers use for the same
            // two questions.
            'item/commandExecution/requestApproval', 'execCommandApproval' => new ApprovalRequest(
                kind: ApprovalRequest::KIND_COMMAND,
                itemId: (string) ($params['itemId'] ?? ''),
                subject: $this->commandSubject($params),
                payload: $params,
                callId: is_string($params['approvalId'] ?? null) ? $params['approvalId'] : null,
            ),
            'item/fileChange/requestApproval', 'applyPatchApproval' => new ApprovalRequest(
                kind: ApprovalRequest::KIND_FILE_CHANGE,
                itemId: (string) ($params['itemId'] ?? ''),
                subject: is_string($params['reason'] ?? null) ? $params['reason'] : '',
                payload: $params,
            ),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function commandSubject(array $params): string
    {
        $command = $params['command'] ?? null;

        if (is_array($command)) {
            $command = implode(' ', array_map('strval', $command));
        }

        return is_string($command) ? $command : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function threadParams(RunRequest $request): array
    {
        return array_filter([
            'cwd' => $request->cwd,
            'model' => $request->model,
            'sandbox' => $request->sandbox->value,

            // `on-request` is what makes the agent ask rather than decide for
            // itself; full access is the one profile where it never should.
            'approvalPolicy' => $request->sandbox === Sandbox::FullAccess
                ? 'never'
                : 'on-request',
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function turnParams(RunRequest $request): array
    {
        $input = [['type' => 'text', 'text' => $request->prompt]];

        foreach ($request->images as $image) {
            $input[] = ['type' => 'localImage', 'path' => $image];
        }

        return array_filter([
            'threadId' => $this->threadId,
            'input' => $input,
            'model' => $request->model,
            'outputSchema' => $request->schema,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * How long to wait for the next message, never past the turn's own deadline.
     */
    private function slice(float $deadline): float
    {
        return max(0.1, min(1.0, $deadline - microtime(true)));
    }

    private function stop(): void
    {
        // Only when the turn is still going. Reaching here after a normal
        // completion is the ordinary case, and interrupting a turn that already
        // ended is a pointless round trip on a connection about to close.
        if (! $this->turnFinished && $this->client !== null && $this->threadId !== null && $this->turnId !== null) {
            $this->client->turnInterrupt($this->threadId, $this->turnId);
        }

        $this->connection?->close();
        $this->connection = null;
        $this->client = null;
        $this->turnId = null;
    }
}
