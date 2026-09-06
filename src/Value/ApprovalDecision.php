<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

/**
 * The answer to one approval request.
 *
 * Codex also accepts session-scoped variants that suppress future prompts. They
 * are deliberately not offered: a decision made here covers exactly the call it
 * was asked about, and nothing is remembered on the user's behalf.
 *
 * `Decline` lets the agent carry on and find another way; `Cancel` ends the
 * turn as well. Denying an action the user was asked about is the former —
 * ending the turn is a separate thing they did not ask for.
 */
enum ApprovalDecision: string
{
    case Accept = 'accept';
    case Decline = 'decline';
    case Cancel = 'cancel';
}
