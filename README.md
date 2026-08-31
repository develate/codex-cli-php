# Codex CLI PHP

A small, resilient PHP SDK for controlling Codex through the interfaces shipped
with Codex itself:

- `codex exec --json` for agent turns, JSONL streaming, threads, resume, fork,
  structured output, and images.
- `codex app-server --stdio` for account information, quota windows, and credits.

The public model is `Codex → Thread → Turn → Events`. CLI commands and app-server
JSON-RPC are transport details behind that interface.

## Requirements

- PHP 8.2 or newer
- A working, authenticated `codex` executable on `PATH`
- Symfony Process (installed by Composer)

```bash
composer require develate/codex-cli-php
```

## Quick start

```php
use Develate\CodexCli\Codex;
use Develate\CodexCli\Value\Sandbox;

$codex = new Codex();

$thread = $codex->thread(
    cwd: '/var/www/project',
    model: 'gpt-5.6-sol',
    sandbox: Sandbox::WorkspaceWrite,
);

$result = $thread->run('Fix the failing tests.');

echo $result->response;
echo $result->usage->inputTokens;
echo $result->usage->outputTokens;
```

The safe default is `Sandbox::ReadOnly`. Write access must be selected explicitly.
Full access is deliberately conspicuous:

```php
$result = $codex
    ->dangerouslyAllowFullAccess()
    ->in('/isolated/runner')
    ->run('Run the migration.');
```

Use full access only inside a controlled environment.

## Streaming and events

Streaming is the primitive; `run()` consumes the same stream and builds a
`TurnResult`.

```php
use Develate\CodexCli\Event\AgentMessage;
use Develate\CodexCli\Event\CommandExecution;
use Develate\CodexCli\Event\FileChange;

foreach ($thread->stream('Fix the tests.') as $event) {
    if ($event instanceof CommandExecution && $event->phase === 'started') {
        echo '$ '.$event->command.PHP_EOL;
    }

    if ($event instanceof FileChange) {
        echo $event->kind->value.': '.$event->path.PHP_EOL;
    }

    if ($event instanceof AgentMessage) {
        echo $event->text;
    }
}

$result = $thread->result();
```

Known events have typed classes. Unknown top-level events and unknown item types
become `UnknownEvent`; additional fields are never rejected. Every event keeps its
original payload through `$event->raw()`.

## Threads, resume, and fork

After the first successful turn, following calls automatically use
`codex exec resume <thread-id>`:

```php
$thread->run('Analyze this project.');
$thread->run('Now fix the problems you found.');

echo $thread->id();
```

An existing thread can be resumed, or a branch can be created lazily. The fork is
performed when the branch receives its first prompt, avoiding an empty Codex turn.

```php
$thread = $codex->resume($threadId);
$branch = $thread->fork();
$branch->run('Try the alternative implementation.');
```

The current Codex CLI does not accept `--cd`, `--sandbox`, or `--add-dir` on its
resume/fork subcommands; those turns retain the thread's persisted runtime state.

## Result and usage

```php
echo $result->response;

$result->commands();
$result->fileChanges();
$result->reasoning();
$result->todoList();
$result->errors();

echo $result->usage->totalTokens();
echo $result->usage->uncachedInputTokens();
echo $result->usage->cacheHitRatio();

echo $result->metadata->codexVersion;
echo $result->metadata->requestedModel;
echo $result->metadata->cwd;
```

Turn usage and account quota are intentionally separate.

## Account quota and credits

```php
$quota = $codex->quota();

echo $quota->primary?->usedPercent;
echo $quota->primary?->remainingPercent();
echo $quota->primary?->resetsAt?->format(DATE_ATOM);

echo $quota->secondary?->remainingPercent();
echo $quota->credits?->balance;
```

Quota windows are generic; the SDK does not assume that the primary or secondary
window has a fixed duration. Additional metered buckets are available through
`$quota->additional`, and the complete app-server response remains in `$quota->raw`.

Account metadata is available separately:

```php
$account = $codex->account();

echo $account->type;
echo $account->planType;
```

## Structured output, images, and writable directories

```php
$result = $thread->run(
    prompt: 'Review this code.',
    schema: [
        'type' => 'object',
        'properties' => [
            'approved' => ['type' => 'boolean'],
        ],
        'required' => ['approved'],
        'additionalProperties' => false,
    ],
    images: ['/tmp/reference.png', '/tmp/detail.png'],
);
```

The schema is written to a per-run temporary file and removed after the process
ends. Multiple writable directories can be granted when creating a thread:

```php
$thread = $codex->thread(
    cwd: '/project',
    sandbox: Sandbox::WorkspaceWrite,
    writableDirectories: ['/shared/generated'],
);
```

## Failure model

Malformed JSONL, process startup/timeout/failure, and JSON-RPC errors raise typed
exceptions under `Develate\CodexCli\Exception`. A syntactically valid event with an
unknown type is not an error.

## Development

```bash
composer install
composer test
```

The transport interfaces are dependency-injectable, so unit tests do not need to
start Codex. See the official documentation for
[`codex exec`](https://developers.openai.com/codex/noninteractive) and the
[`codex app-server`](https://developers.openai.com/codex/app-server).
