<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\Value\ElicitationAction;

final readonly class ElicitationResponse
{
    /** @param array<string, mixed>|null $content */
    public function __construct(
        public ElicitationAction $action,
        public ?array $content = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = ['action' => $this->action->value];

        if ($this->action === ElicitationAction::Accept && $this->content !== null) {
            $result['content'] = (object) $this->content;
        }

        return $result;
    }
}
