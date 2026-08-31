<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Support;

use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\ThreadStarted;
use Develate\CodexCli\Event\TurnCompleted;
use Develate\CodexCli\Process\ProcessResult;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Transport\Transport;
use Develate\CodexCli\Value\Usage;

final class FakeTransport implements Transport
{
    /** @var list<RunRequest> */
    public array $requests = [];

    public function stream(RunRequest $request): \Generator
    {
        $this->requests[] = $request;
        $threadId = $request->mode === RunMode::Fork ? 'thread-child' : ($request->threadId ?? 'thread-one');

        yield new ThreadStarted($threadId, ['type' => 'thread.started', 'thread_id' => $threadId]);
        yield new AgentMessage('message-1', 'answer: '.$request->prompt, 'completed', [
            'type' => 'item.completed',
            'item' => ['id' => 'message-1', 'type' => 'agent_message', 'text' => 'answer: '.$request->prompt],
        ]);
        yield new TurnCompleted(new Usage(inputTokens: 10, outputTokens: 2), [
            'type' => 'turn.completed',
            'usage' => ['input_tokens' => 10, 'output_tokens' => 2],
        ]);

        return new ProcessResult(0, '');
    }
}
