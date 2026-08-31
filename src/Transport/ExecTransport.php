<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\Parser\JsonlEventParser;
use Develate\CodexCli\Process\ProcessFactory;
use Develate\CodexCli\Process\ProcessResult;

final class ExecTransport implements Transport
{
    public function __construct(
        private readonly string $binary = 'codex',
        private readonly ProcessFactory $processFactory = new ProcessFactory(),
        private readonly JsonlEventParser $parser = new JsonlEventParser(),
    ) {
    }

    public function stream(RunRequest $request): \Generator
    {
        $schemaFile = $request->schema === null ? null : $this->writeSchema($request->schema);

        try {
            $process = $this->processFactory->create(
                $this->command($request, $schemaFile),
                $request->cwd,
                $request->timeout,
            );
            $lines = $process->lines();

            foreach ($lines as $line) {
                foreach ($this->parser->parseLine($line) as $event) {
                    yield $event;
                }
            }

            /** @var ProcessResult $result */
            $result = $lines->getReturn();

            return $result;
        } finally {
            if ($schemaFile !== null && is_file($schemaFile)) {
                @unlink($schemaFile);
            }
        }
    }

    /** @return list<string> */
    public function command(RunRequest $request, ?string $schemaFile = null): array
    {
        $command = [$this->binary, 'exec'];
        if ($request->mode === RunMode::Resume) {
            $command[] = 'resume';
        } elseif ($request->mode === RunMode::Fork) {
            $command[] = 'fork';
        }

        $command[] = '--json';

        if ($request->mode === RunMode::Start) {
            $command[] = '--color';
            $command[] = 'never';
            $command[] = '--cd';
            $command[] = $request->cwd;
            $command[] = '--sandbox';
            $command[] = $request->sandbox->value;
            foreach ($request->writableDirectories as $directory) {
                $command[] = '--add-dir';
                $command[] = $directory;
            }
        }

        if ($request->model !== null) {
            $command[] = '--model';
            $command[] = $request->model;
        }
        foreach ($request->images as $image) {
            $command[] = '--image';
            $command[] = $image;
        }
        if ($schemaFile !== null) {
            $command[] = '--output-schema';
            $command[] = $schemaFile;
        }
        if ($request->mode !== RunMode::Start) {
            if ($request->threadId === null || $request->threadId === '') {
                throw new \LogicException('Resume and fork runs require a thread id.');
            }
            $command[] = $request->threadId;
        }
        $command[] = $request->prompt;

        return $command;
    }

    /** @param array<string, mixed> $schema */
    private function writeSchema(array $schema): string
    {
        $path = tempnam(sys_get_temp_dir(), 'codex-schema-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create a temporary output schema file.');
        }

        try {
            $json = json_encode($schema, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($path, $json) === false) {
                throw new \RuntimeException('Unable to write the temporary output schema file.');
            }
        } catch (\Throwable $exception) {
            @unlink($path);
            throw $exception;
        }

        return $path;
    }
}
