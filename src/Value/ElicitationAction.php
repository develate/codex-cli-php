<?php

declare(strict_types=1);

namespace Develate\CodexCli\Value;

/**
 * What a person did with a form an MCP server put in front of them.
 *
 * `Accept` is the only one that carries content back. The other two are not
 * interchangeable: `Decline` is an answer — the user read the request and said
 * no, and the server is told so — while `Cancel` says nobody answered at all,
 * which is what a dismissed sheet, a closed window or a client that cannot
 * render the form has to report.
 *
 * @see https://modelcontextprotocol.io/specification/2025-11-25/client/elicitation
 */
enum ElicitationAction: string
{
    case Accept = 'accept';
    case Decline = 'decline';
    case Cancel = 'cancel';
}
