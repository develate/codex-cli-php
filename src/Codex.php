<?php

declare(strict_types=1);

namespace Develate\CodexCli;

use Develate\CodexCli\Exception\CodexNotFound;
use Develate\CodexCli\Exception\ProcessFailed;
use Develate\CodexCli\Process\CodexExecutable;
use Develate\CodexCli\Transport\AppServerTransport;
use Develate\CodexCli\Transport\AppServerTurnTransport;
use Develate\CodexCli\Transport\ExecTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\Transport;
use Develate\CodexCli\Value\Account;
use Develate\CodexCli\Value\ConsumeRateLimitResetCreditResult;
use Develate\CodexCli\Value\Quota;
use Develate\CodexCli\Value\Sandbox;
use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailed;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyRuntimeException;
use Symfony\Component\Process\Process;

final class Codex
{
    private readonly Transport $execTransport;
    private readonly AppServerTransport $appServerTransport;
    private readonly ?AppServerTurnTransport $turnTransport;
    private ?string $versionCache = null;
    private ?string $defaultCwd = null;
    private Sandbox $defaultSandbox = Sandbox::ReadOnly;

    public function __construct(
        private readonly string $binary = 'codex',
        ?Transport $execTransport = null,
        ?AppServerTransport $appServerTransport = null,
        private readonly ?float $timeout = null,
        ?AppServerTurnTransport $turnTransport = null,
    ) {
        $this->execTransport = $execTransport ?? new ExecTransport($binary);
        $this->appServerTransport = $appServerTransport ?? new AppServerTransport($binary);
        $this->turnTransport = $turnTransport;
    }

    /**
     * Whether turns can be driven interactively, so the agent is able to stop
     * and ask rather than deciding on its own.
     */
    public function supportsInteractiveTurns(): bool
    {
        return $this->turnTransport !== null;
    }

    /**
     * The transport an interactive turn runs on, so the caller can register how
     * approvals are answered before starting one.
     */
    public function turnTransport(): ?AppServerTurnTransport
    {
        return $this->turnTransport;
    }

    /**
     * @param list<string> $writableDirectories
     */
    public function thread(
        ?string $cwd = null,
        ?string $model = null,
        ?Sandbox $sandbox = null,
        array $writableDirectories = [],
        bool $interactive = false,
    ): Thread {
        return new Thread(
            transport: $this->transportFor($interactive),
            codexVersion: $this->version(),
            cwd: $this->resolveCwd($cwd),
            model: $model,
            sandbox: $sandbox ?? $this->defaultSandbox,
            writableDirectories: $writableDirectories,
            timeout: $this->timeout,
        );
    }

    public function resume(string $id, ?string $cwd = null, ?string $model = null, bool $interactive = false, ?Sandbox $sandbox = null): Thread
    {
        return new Thread(
            transport: $this->transportFor($interactive),
            codexVersion: $this->version(),
            cwd: $this->resolveCwd($cwd),
            model: $model,
            sandbox: $sandbox ?? $this->defaultSandbox,
            timeout: $this->timeout,
            threadId: $id,
            nextMode: RunMode::Resume,
        );
    }

    public function in(string $cwd): self
    {
        $clone = clone $this;
        $clone->defaultCwd = $cwd;

        return $clone;
    }

    public function dangerouslyAllowFullAccess(): self
    {
        $clone = clone $this;
        $clone->defaultSandbox = Sandbox::FullAccess;

        return $clone;
    }

    /**
     * @param array<string, mixed>|null $schema
     * @param list<string> $images
     */
    public function run(string $prompt, ?array $schema = null, array $images = []): TurnResult
    {
        return $this->thread()->run($prompt, $schema, $images);
    }

    public function quota(): Quota
    {
        return $this->appServerTransport->quota();
    }

    public function account(): Account
    {
        return $this->appServerTransport->account();
    }

    public function consumeRateLimitResetCredit(string $idempotencyKey, ?string $creditId = null): ConsumeRateLimitResetCreditResult
    {
        return $this->appServerTransport->consumeRateLimitResetCredit($idempotencyKey, $creditId);
    }

    public function version(): string
    {
        if ($this->versionCache !== null) {
            return $this->versionCache;
        }

        CodexExecutable::assertAvailable($this->binary);
        try {
            $process = new Process([$this->binary, '--version'], timeout: 5.0);
            $process->mustRun();
        } catch (SymfonyProcessFailed $exception) {
            throw new ProcessFailed($exception->getProcess()->getExitCode() ?? 1, $exception->getProcess()->getErrorOutput());
        } catch (SymfonyRuntimeException $exception) {
            throw new CodexNotFound(sprintf('Codex binary "%s" was not found.', $this->binary), 0, $exception);
        }

        $output = trim($process->getOutput());
        if (preg_match('/(?:codex-cli\s+)?([0-9]+(?:\.[0-9]+){1,3}(?:[-+][^\s]+)?)/', $output, $matches) === 1) {
            return $this->versionCache = $matches[1];
        }

        return $this->versionCache = $output;
    }

    /**
     * Interactive turns fall back to `codex exec` rather than failing when the
     * installed CLI has no app-server turn support: the turn still runs, and
     * the caller has already been told through {@see self::supportsInteractiveTurns()}
     * that approvals will not be possible.
     */
    private function transportFor(bool $interactive): Transport
    {
        return $interactive && $this->turnTransport !== null
            ? $this->turnTransport
            : $this->execTransport;
    }

    private function resolveCwd(?string $cwd): string
    {
        $resolved = $cwd ?? $this->defaultCwd ?? getcwd();
        if (!is_string($resolved) || $resolved === '') {
            throw new \RuntimeException('Unable to determine the working directory.');
        }

        return $resolved;
    }
}
