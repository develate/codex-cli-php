# Changelog

## 2026-10-03

- Fix interactive forks to call app-server `thread/fork` and resume the returned child. Normalize app-server command statuses and patch kinds, preserve complete original notification payloads, and report interrupted completions through `TurnFailed` with their upstream status.
- Add `Thread::steer()` and app-server `turn/steer` support with active-turn validation and text/image input. Interactive transports expose `turnId()` and `onTick()` so clients can deliver steering while Codex is thinking, and buffered completion ends the stream without accepting further input.

## 2026-09-28

- Keep sub-agent threads from ending or speaking for an interactive turn. A spawned agent's `turn/completed` used to finish the stream and close the connection, which aborted the root turn mid-work, and its messages and items arrived as the root agent's own. Their spawn calls, token usage and reroutes are still streamed.

## 2026-09-27

- Derive interactive turn usage from app-server token updates so completed turns report their token counts even though `turn/completed` has no usage field. Repeated and out-of-order updates do not double count.

## 2026-09-22

- Translate account/rateLimits/updated notifications into typed AccountRateLimitsUpdated quota events, preserving the raw notification.

## 1.7.0 - 2026-09-19

- Accept a per-process environment on `Codex` and on the exec, app-server and app-server-turn transports, matching the `array<string, string|false>` convention of the other Develate CLI SDKs: a `false` entry removes a variable the parent would otherwise pass on. The default is unchanged — with no environment given, a Codex process inherits exactly what it inherited before.

## 2026-09-14

- Expose Codex app-server collaboration, model-reroute, lifecycle, and per-thread token-usage protocol events while retaining unrecognised notifications as raw events.
