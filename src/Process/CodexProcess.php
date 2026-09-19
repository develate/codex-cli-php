<?php

declare(strict_types=1);

namespace Develate\CodexCli\Process;

use Develate\CodexCli\Exception\ProcessTimedOut;
use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyProcessTimedOut;
use Symfony\Component\Process\Process;

final class CodexProcess
{
    private string $stderr = '';

    /**
     * @param list<string> $command
     * @param array<string, string|false> $env variables for the child; `false` removes an inherited one
     */
    public function __construct(
        private readonly array $command,
        private readonly ?string $cwd = null,
        private readonly ?float $timeout = null,
        private readonly array $env = [],
    ) {
    }

    /** @return \Generator<int, string, void, ProcessResult> */
    public function lines(): \Generator
    {
        CodexExecutable::assertAvailable($this->command[0]);
        $process = new Process($this->command, $this->cwd, $this->env, timeout: $this->timeout);
        $reader = new JsonlReader();

        try {
            $process->start();
            foreach ($process as $type => $chunk) {
                if ($type === Process::ERR) {
                    $this->stderr .= $chunk;
                    continue;
                }

                foreach ($reader->push($chunk) as $line) {
                    yield $line;
                }
            }
        } catch (SymfonyProcessTimedOut $exception) {
            throw new ProcessTimedOut($exception->getMessage(), 0, $exception);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            if ($process->isRunning()) {
                $process->stop(1.0);
            }
        }

        if (($last = $reader->finish()) !== null) {
            yield $last;
        }

        return new ProcessResult($process->getExitCode() ?? 1, $this->stderr);
    }
}
