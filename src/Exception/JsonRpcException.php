<?php

declare(strict_types=1);

namespace Develate\CodexCli\Exception;

final class JsonRpcException extends AppServerException
{
    public function __construct(
        string $message,
        public readonly ?int $rpcCode = null,
        public readonly mixed $data = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $rpcCode ?? 0, $previous);
    }
}
