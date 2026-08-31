<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

use Develate\CodexCli\Exception\AppServerException;
use Develate\CodexCli\Exception\CodexNotFound;
use Develate\CodexCli\Exception\JsonRpcException;
use Develate\CodexCli\Process\CodexExecutable;

final class JsonRpcConnection
{
    /** @var resource|null */
    private $process = null;

    /** @var array<int, resource> */
    private array $pipes = [];

    private int $nextId = 1;

    /** @var list<callable(Notification): void> */
    private array $notificationListeners = [];

    public function __construct(
        private readonly string $binary = 'codex',
        private readonly float $timeout = 10.0,
    ) {
    }

    public function connect(): void
    {
        if (is_resource($this->process)) {
            return;
        }

        CodexExecutable::assertAvailable($this->binary);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = @proc_open([$this->binary, 'app-server', '--stdio'], $descriptors, $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            throw new CodexNotFound(sprintf('Unable to start Codex binary "%s".', $this->binary));
        }

        $this->process = $process;
        $this->pipes = $pipes;
        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
    }

    /** @param array<string, mixed>|null $params */
    public function request(string $method, ?array $params = null): mixed
    {
        $this->connect();
        $request = new Request($this->nextId++, $method, $params);
        $this->write($request->toArray());

        while (true) {
            $message = $this->read();
            if (isset($message['method']) && !isset($message['id'])) {
                $this->dispatchNotification($message);
                continue;
            }
            if (($message['id'] ?? null) !== $request->id) {
                continue;
            }

            $response = new Response(
                id: $request->id,
                result: $message['result'] ?? null,
                error: is_array($message['error'] ?? null) ? $message['error'] : null,
                raw: $message,
            );
            if ($response->error !== null) {
                throw new JsonRpcException(
                    message: is_string($response->error['message'] ?? null) ? $response->error['message'] : 'App server request failed.',
                    rpcCode: is_numeric($response->error['code'] ?? null) ? (int) $response->error['code'] : null,
                    data: $response->error['data'] ?? null,
                );
            }

            return $response->result;
        }
    }

    /** @param array<string, mixed>|null $params */
    public function notify(string $method, ?array $params = null): void
    {
        $this->connect();
        $message = ['method' => $method];
        if ($params !== null) {
            $message['params'] = $params;
        }
        $this->write($message);
    }

    /** @param callable(Notification): void $listener */
    public function onNotification(callable $listener): void
    {
        $this->notificationListeners[] = $listener;
    }

    public function close(): void
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = [];

        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);
            if ($status['running'] ?? false) {
                proc_terminate($this->process);
            }
            proc_close($this->process);
        }
        $this->process = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /** @param array<string, mixed> $message */
    private function write(array $message): void
    {
        $encoded = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n";
        $written = 0;
        while ($written < strlen($encoded)) {
            $bytes = fwrite($this->pipes[0], substr($encoded, $written));
            if ($bytes === false || $bytes === 0) {
                throw new AppServerException('Unable to write to the Codex app server.');
            }
            $written += $bytes;
        }
        fflush($this->pipes[0]);
    }

    /** @return array<string, mixed> */
    private function read(): array
    {
        $deadline = microtime(true) + $this->timeout;
        $buffer = '';

        while (microtime(true) < $deadline) {
            $read = [$this->pipes[1]];
            $write = null;
            $except = null;
            $remaining = max(0.0, $deadline - microtime(true));
            $seconds = (int) $remaining;
            $microseconds = (int) (($remaining - $seconds) * 1_000_000);
            $ready = @stream_select($read, $write, $except, $seconds, $microseconds);
            if ($ready === false) {
                throw new AppServerException('Failed while waiting for the Codex app server.');
            }
            if ($ready === 0) {
                continue;
            }

            $chunk = fgets($this->pipes[1]);
            if ($chunk === false) {
                if (feof($this->pipes[1])) {
                    throw new AppServerException('Codex app server closed its output. '.$this->stderr());
                }
                continue;
            }
            $buffer .= $chunk;
            if (!str_ends_with($buffer, "\n")) {
                continue;
            }

            try {
                $message = json_decode(trim($buffer), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new JsonRpcException('Codex app server emitted invalid JSON.', previous: $exception);
            }
            if (!is_array($message)) {
                throw new JsonRpcException('Codex app server emitted a non-object message.');
            }

            return $message;
        }

        throw new AppServerException(sprintf('Codex app server did not respond within %.1f seconds. %s', $this->timeout, $this->stderr()));
    }

    /** @param array<string, mixed> $message */
    private function dispatchNotification(array $message): void
    {
        $notification = new Notification(
            method: is_string($message['method'] ?? null) ? $message['method'] : '',
            params: is_array($message['params'] ?? null) ? $message['params'] : [],
            raw: $message,
        );
        foreach ($this->notificationListeners as $listener) {
            $listener($notification);
        }
    }

    private function stderr(): string
    {
        return isset($this->pipes[2]) && is_resource($this->pipes[2]) ? trim((string) stream_get_contents($this->pipes[2])) : '';
    }
}
