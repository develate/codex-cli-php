<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

/**
 * A form an MCP server is holding the turn open for.
 *
 * Not an approval, though it arrives on the same channel and blocks in the same
 * way. An approval asks whether one thing may happen and is answered yes or no;
 * this asks a person to fill something in — a branch name, a choice
 * from a list — and the answer travels back as structured content.
 *
 * Two shapes reach a client that has not opted into OpenAI's own form
 * rendering. `form` carries a small JSON Schema describing the fields; `url`
 * carries somewhere the user has to go instead, which is how an MCP server
 * asks them to sign in.
 *
 * @see https://developers.openai.com/codex/app-server
 * @see https://modelcontextprotocol.io/specification/2025-11-25/client/elicitation
 */
final readonly class ElicitationRequest
{
    public const MODE_FORM = 'form';

    public const MODE_URL = 'url';

    /**
     * @param  array<string, mixed>  $schema  The requested fields, in `form` mode.
     * @param  array<string, mixed>  $payload  The request as it arrived.
     */
    public function __construct(
        public string $mode,
        public string $serverName,
        public string $message,
        public array $schema = [],
        public ?string $url = null,
        public ?string $elicitationId = null,
        public ?string $threadId = null,
        public ?string $turnId = null,
        public array $payload = [],
    ) {}

    /**
     * Reads one off the wire, or returns null for a form this client cannot put
     * in front of anybody.
     *
     * The unreadable case is real rather than theoretical: `openai/form` mode
     * carries a schema this client has no renderer for. It is only ever sent to
     * a client that asked for it during `initialize`, and this one does not, but
     * a request that arrives anyway has to be reported as unanswered rather than
     * guessed at.
     *
     * @param  array<string, mixed>  $params
     */
    public static function fromParams(array $params): ?self
    {
        $mode = is_string($params['mode'] ?? null) ? $params['mode'] : null;
        $schema = is_array($params['requestedSchema'] ?? null) ? $params['requestedSchema'] : [];

        // Older servers describe a plain MCP form by sending the schema alone.
        $mode ??= $schema === [] ? '' : self::MODE_FORM;

        if ($mode === self::MODE_URL) {
            $url = is_string($params['url'] ?? null) ? $params['url'] : '';

            if (! in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) || filter_var($url, FILTER_VALIDATE_URL) === false) {
                return null;
            }

            return new self(
                mode: self::MODE_URL,
                serverName: self::text($params, 'serverName'),
                message: self::text($params, 'message'),
                url: $url,
                elicitationId: self::optionalText($params, 'elicitationId'),
                threadId: self::optionalText($params, 'threadId'),
                turnId: self::optionalText($params, 'turnId'),
                payload: $params,
            );
        }

        if ($mode !== self::MODE_FORM || ! ElicitationSchema::supports($schema)) {
            return null;
        }

        return new self(
            mode: self::MODE_FORM,
            serverName: self::text($params, 'serverName'),
            message: self::text($params, 'message'),
            schema: $schema,
            elicitationId: self::optionalText($params, 'elicitationId'),
            threadId: self::optionalText($params, 'threadId'),
            turnId: self::optionalText($params, 'turnId'),
            payload: $params,
        );
    }

    /** @param array<string, mixed> $params */
    private static function text(array $params, string $key): string
    {
        return is_string($params[$key] ?? null) ? $params[$key] : '';
    }

    /** @param array<string, mixed> $params */
    private static function optionalText(array $params, string $key): ?string
    {
        return is_string($params[$key] ?? null) && $params[$key] !== '' ? $params[$key] : null;
    }
}
