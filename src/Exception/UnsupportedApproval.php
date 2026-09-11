<?php

declare(strict_types=1);

namespace Develate\CodexCli\Exception;

/**
 * The app server asked for an approval or permission this client cannot present.
 *
 * Raised rather than answered. Auto-accepting an unknown request would grant
 * access nobody reviewed, and auto-declining would look like a user decision;
 * failing the turn visibly is the only honest option.
 */
final class UnsupportedApproval extends CodexException
{
    public function __construct(public readonly string $method)
    {
        parent::__construct(sprintf(
            'The Codex app server requested an approval this client does not support: "%s".',
            $method,
        ));
    }
}
