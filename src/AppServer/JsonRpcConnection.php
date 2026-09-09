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

    private string $readBuffer = '';

    /** @var list<callable(Notification): void> */
    private array $notificationListeners = [];

    /** @var (callable(ServerRequest): array<string, mixed>)|null */
    private $serverRequestHandler = null;

    /** @var list<Notification> */
    private array $inbox = [];

    /** @param array<string, string> $configOverrides */
    public function __construct(
        private readonly string $binary = 'codex',
        private readonly float $timeout = 10.0,
        private readonly array $configOverrides = [],
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
        $command = [$this->binary, 'app-server', '--stdio'];
        foreach ($this->configOverrides as $key => $value) {
            $command[] = '-c';
            $command[] = $key.'='.$value;
        }
        $process = @proc_open($command, $descriptors, $pipes, null, null, ['bypass_shell' => true]);
        if (! is_resource($process)) {
            throw new CodexNotFound(sprintf('Unable to start Codex binary "%s".', $this->binary));
        }

        $this->process = $process;
        $this->pipes = $pipes;
        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
    }

    /** @param array<string, mixed>|null $params */
    public function request(string $method, ?array $params = null, ?float $timeout = null): mixed
    {
        $this->connect();
        $request = new Request($this->nextId++, $method, $params);
        $this->write($request->toArray());

        while (true) {
            $message = $this->read($timeout);
            if ($this->handleIncoming($message)) {
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

    /**
     * Registers the answer to requests the server sends us.
     *
     * The handler returns the JSON-RPC result. Throwing from it turns into an
     * error response rather than silence, because a server request nobody
     * replies to leaves the agent waiting for the rest of the turn.
     *
     * @param  callable(ServerRequest): array<string, mixed>  $handler
     */
    public function onServerRequest(callable $handler): void
    {
        $this->serverRequestHandler = $handler;
    }

    /**
     * Waits for one message and files it, without expecting a response.
     *
     * This is what lets a turn stream: notifications pile up in the inbox and
     * server requests are answered as they arrive, while the caller decides how
     * long to keep listening.
     *
     * @return bool whether a message arrived before the timeout
     */
    public function pump(float $timeout): bool
    {
        $this->connect();

        try {
            $message = $this->read($timeout);
        } catch (AppServerException $exception) {
            // A timeout is a normal outcome here: the agent may simply be
            // thinking. Anything else is a real transport failure.
            if (str_contains($exception->getMessage(), 'did not respond within')) {
                return false;
            }

            throw $exception;
        }

        $this->handleIncoming($message);

        return true;
    }

    /**
     * Takes everything received so far, oldest first.
     *
     * @return list<Notification>
     */
    public function drain(): array
    {
        $inbox = $this->inbox;
        $this->inbox = [];

        return $inbox;
    }

    /**
     * Files a message that is not the response we are waiting for.
     *
     * @param  array<string, mixed>  $message
     * @return bool whether the message was consumed here
     */
    private function handleIncoming(array $message): bool
    {
        $isRequest = isset($message['method']) && isset($message['id']);

        if ($isRequest) {
            $this->answer(ServerRequest::fromMessage($message));

            return true;
        }

        if (isset($message['method'])) {
            $this->dispatchNotification($message);

            return true;
        }

        return false;
    }

    private function answer(ServerRequest $request): void
    {
        if ($this->serverRequestHandler === null) {
            $this->write([
                'id' => $request->id,
                'error' => ['code' => -32601, 'message' => 'This client does not accept server requests.'],
            ]);

            return;
        }

        try {
            $result = ($this->serverRequestHandler)($request);
        } catch (\Throwable $exception) {
            $this->write([
                'id' => $request->id,
                'error' => ['code' => -32603, 'message' => $exception->getMessage()],
            ]);

            throw $exception;
        }

        $this->write(['id' => $request->id, 'result' => $result]);
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
        $this->readBuffer = '';
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
    private function read(?float $timeout = null): array
    {
        $timeout ??= $this->timeout;
        $deadline = microtime(true) + $timeout;

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
            $this->readBuffer .= $chunk;
            if (! str_ends_with($this->readBuffer, "\n")) {
                continue;
            }

            try {
                $buffer = $this->readBuffer;
                $this->readBuffer = '';
                $message = json_decode(trim($buffer), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new JsonRpcException('Codex app server emitted invalid JSON.', previous: $exception);
            }
            if (! is_array($message)) {
                throw new JsonRpcException('Codex app server emitted a non-object message.');
            }

            return $message;
        }

        throw new AppServerException(sprintf('Codex app server did not respond within %.1f seconds. %s', $timeout, $this->stderr()));
    }

    /** @param array<string, mixed> $message */
    private function dispatchNotification(array $message): void
    {
        $notification = new Notification(
            method: is_string($message['method'] ?? null) ? $message['method'] : '',
            params: is_array($message['params'] ?? null) ? $message['params'] : [],
            raw: $message,
        );
        $this->inbox[] = $notification;

        foreach ($this->notificationListeners as $listener) {
            $listener($notification);
        }
    }

    private function stderr(): string
    {
        return isset($this->pipes[2]) && is_resource($this->pipes[2]) ? trim((string) stream_get_contents($this->pipes[2])) : '';
    }
}
