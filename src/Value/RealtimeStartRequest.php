<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

final readonly class RealtimeStartRequest
{
    public function __construct(
        public string $threadId,
        public RealtimeOutputModality $outputModality = RealtimeOutputModality::Audio,
        public ?RealtimeTransport $transport = null,
        public ?string $prompt = null,
        public ?string $realtimeSessionId = null,
        public ?string $model = null,
        public ?string $version = null,
        public ?bool $includeStartupContext = null,
        public ?bool $clientManagedHandoffs = null,
        public ?string $voice = null,
        public bool $omitPrompt = true,
    ) {
        if ($threadId === '') {
            throw new \InvalidArgumentException('The realtime thread ID must not be empty.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $params = [
            'threadId' => $this->threadId,
            'outputModality' => $this->outputModality->value,
            'prompt' => $this->prompt,
            'realtimeSessionId' => $this->realtimeSessionId,
            'model' => $this->model,
            'version' => $this->version,
            'includeStartupContext' => $this->includeStartupContext,
            'clientManagedHandoffs' => $this->clientManagedHandoffs,
            'voice' => $this->voice,
        ];
        if ($this->transport !== null) {
            $params['transport'] = $this->transport->toArray();
        }

        $params = array_filter($params, static fn (mixed $value): bool => $value !== null);
        if (!$this->omitPrompt) {
            $params['prompt'] = $this->prompt;
        }
        return $params;
    }
}
