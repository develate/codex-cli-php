<?php

declare(strict_types=1);

namespace Develate\CodexCli\AppServer;

final readonly class Request
{
    /** @param array<string, mixed>|null $params */
    public function __construct(public int $id, public string $method, public ?array $params = null)
    {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $request = ['method' => $this->method, 'id' => $this->id];
        if ($this->params !== null) {
            $request['params'] = $this->params;
        }

        return $request;
    }
}
