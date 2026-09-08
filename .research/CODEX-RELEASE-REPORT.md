# Codex CLI Release Compatibility Report — `develate/codex-cli-php`

## Verdict: Update recommended

The default `codex exec --json` path is fully compatible with the latest
stable CLI. Three gaps exist on the opt-in interactive (`app-server turn`)
path; none is caused by a newly released stable version, and none affects
`ExecTransport`, quota/account reads, or reset-credit consumption. The
recommendation is driven by real functional gaps against latest-stable
interfaces, not by the existence of a newer upstream version.

## Assessment metadata

- Date of last substantive assessment: 2026-09-08 (first report).
- Working tree: workspace root is not a git checkout (`git rev-parse`
  fails), and `code-cli/codex-cli-php` has no repository of its own, so no
  commit revision can be recorded. Assessed files are the on-disk state of
  `code-cli/codex-cli-php/{src,tests,README.md,composer.json}` on 2026-09-08.
- Validation run at assessment time: `phpunit` — OK (50 tests, 127 assertions)
  on PHP 8.4.23.

## Version distinctions

- PHP package version: unversioned library (`composer.json` carries no
  version field); `AppServerClient::initialize()` identifies itself as
  `develate_codex_cli_php` version `0.1.0`.
  (`src/AppServer/AppServerClient.php:23-29`)
- Locally installed CLI version: `0.153.4` (`codex --version`, verified this run).
- Latest reviewed stable upstream release: `0.153.4`
  (`rust-v0.153.4`, published 2026-09-04T23:25:48Z).
- Supported compatibility baseline: **unclear — no baseline is declared
  anywhere in the package.** There is no minimum-version constant, no
  `UnsupportedCodexVersion` check is wired into any transport or `Codex`
  entry point (`src/Exception/UnsupportedCodexVersion.php` exists but has no
  references), and neither `README.md` nor `composer.json` names a supported
  CLI range. The implementation was therefore assessed directly against the
  latest stable interfaces (`rust-v0.153.4` tag) instead of a baseline delta.

## Reviewed release range

- First run: enumerated all 1000 newest upstream releases via the GitHub
  Releases API (pages 1–10; the API caps at 1000 results), filtered to
  stable only — `draft=false`, `prerelease=false`, tag without
  `alpha|beta|rc|preview|nightly|dev` after normalizing the `rust-v` prefix —
  yielding **142 stable releases**, newest `0.153.4`, oldest in window below
  `0.113.0`.
- Read full release notes for `0.147.0` through `0.153.4` (14 stable
  releases). Older stable notes were scanned by keyword
  (`request_permissions`, approval, schema, JSONL) where needed to date
  findings.
- Verified implementation details against upstream sources at tag
  `rust-v0.153.4`: `codex-rs/exec/src/cli.rs`, `codex-rs/exec/src/exec_events.rs`,
  `codex-rs/app-server-protocol/src/protocol/v2/{thread,turn,item,account}.rs`,
  `codex-rs/protocol/src/{protocol,config_types}.rs`, the official
  [app-server docs](https://learn.chatgpt.com/docs/app-server) (fetched
  2026-09-08), and the locally installed `codex 0.153.4 --help` output.

## Findings — verified compatible (no action)

1. **Exec command construction** (`src/Transport/ExecTransport.php:50-95`).
   Start (`--json --color never --cd --sandbox [--add-dir]… [--model]
   [--image]… [--output-schema]`) matches `Cli`/`SharedCliOptions` in
   `codex-rs/exec/src/cli.rs` (`--output-schema`, `--json`, `--color` global;
   `--image` valid on start). Resume/fork
   (`exec (resume|fork) --json [--model] [--image]… [--output-schema]
   <thread-id> <prompt>`) matches `ResumeArgs`/`ForkArgs` (positional
   session id + prompt, local `--image`, global `--model`/`--output-schema`/
   `--json`). Confirmed against installed CLI:
   `codex exec resume --help` lists exactly those flags and **not**
   `--sandbox`/`--cd`/`--add-dir`, so the `README.md` restriction
   ("resume/fork … retain the thread's persisted runtime state") is accurate
   for 0.153.4.
2. **JSONL envelope and item payloads** (`src/Parser/JsonlEventParser.php`).
   All eight `ThreadEvent` variants in
   `codex-rs/exec/src/exec_events.rs` (`thread.started`, `turn.started`,
   `turn.completed`, `turn.failed`, `item.started/updated/completed`, `error`)
   are handled. Field mapping matches: `usage{input_tokens,
   cached_input_tokens, cache_write_input_tokens, output_tokens,
   reasoning_output_tokens}` (`src/Value/Usage.php:22-26`),
   `command_execution{command, aggregated_output, exit_code, status}`,
   `file_change{changes[{path, kind}]}`. Upstream-only item types
   (`mcp_tool_call`, `collab_tool_call`, `web_search`, `error`, `todo_list`
   handled; `plan_update` alias retained) safely degrade to `UnknownEvent`
   with `raw()` preserved — additive upstream changes are tolerated, per the
   package's documented contract.
3. **Sandbox and approval-policy values.** `Sandbox::{ReadOnly,
   WorkspaceWrite, FullAccess}` (`src/Value/Sandbox.php`) serializes exactly
   as upstream `SandboxMode` (`read-only`, `workspace-write`,
   `danger-full-access`; `config_types.rs:104-114`). `approvalPolicy`
   `on-request`/`never` matches `AskForApproval` kebab-case
   (`protocol.rs:984+`).
4. **App-server thread/turn calls**
   (`src/AppServer/AppServerClient.php`, `src/Transport/AppServerTurnTransport.php:257-289`).
   `thread/start{model, cwd, sandbox, approvalPolicy}`,
   `thread/resume{threadId, excludeTurns}`, `turn/start{threadId, input[],
   model, outputSchema}`, `turn/interrupt{threadId, turnId}` all match
   `ThreadStartParams`/`ThreadResumeParams`/`TurnStartParams` (camelCase) at
   0.153.4. `input: [{type: 'localImage', path}]` matches
   `UserInput::LocalImage` (`tag="type", rename_all="camelCase"`,
   `v2/turn.rs:389-393`). `excludeTurns: true` remains a supported,
   non-experimental resume optimization.
5. **Accounts, quotas, credits**
   (`src/Transport/AppServerTransport.php`, `src/AppServer/QuotaMapper.php`).
   `account/read`, `account/rateLimits/read`, and
   `account/rateLimitResetCredit/consume{idempotencyKey, creditId}` match the
   documented methods; `GetAccountRateLimitsResponse{rateLimits,
   rateLimitsByLimitId, rateLimitResetCredits}` (`v2/account.rs:314-325`)
   matches the mapper, including the nullable-`credits` distinction the
   mapper already encodes. All four
   `ConsumeAccountRateLimitResetCreditOutcome` variants
   (`reset|nothingToReset|noCredit|alreadyRedeemed`, `v2/account.rs:403-411`)
   are covered by `src/Value/ConsumeRateLimitResetCreditOutcome.php`.
   Newer additive fields (`accountId`, `rateLimitUpsell`, credit
   `title`/`description`) flow through `raw` and need no mapping change.
6. **Recent stable releases require no PHP change.**
   - `0.153.0`–`0.153.4`: model-catalog/Astra, TUI/vim, Guardian, plugin
     changes; the only protocol touch is nullable thread-metadata
     `model`/`reasoningEffort` and model-gated `request_user_input_async`
     ([release](https://github.com/openai/codex/releases/tag/rust-v0.153.0)).
     The package only reads `thread.id` from start/resume responses, so the
     nullable additions are inert; async user-input arrives (if at all) via
     the experimental `request_user_input` item flow, which degrades to
     `UnknownEvent`, not a failure, on the exec path.
   - `0.152.0` ([release](https://github.com/openai/codex/releases/tag/rust-v0.152.0)):
     configurable `thread/shellCommand` timeouts and credential-refresh
     progress are server-side/CLI-only; "resumed threads restore saved cwd"
     aligns with the package not sending `--cd` on resume.
   - `0.150.x`–`0.151.0`: TUI/MCP/sandbox items; no `exec --json` or
     consumed app-server surface change.

## Findings — recommended actions (optional, interactive path only)

1. **Unhandled `item/permissions/requestApproval` server request.**
   When the agent uses the built-in `request_permissions` tool, the server
   sends `item/permissions/requestApproval`
   ([docs](https://learn.chatgpt.com/docs/app-server)).
   `AppServerTurnTransport::approvalRequest()`
   (`src/Transport/AppServerTurnTransport.php:216-238`) returns `null` for it
   and `answer()` throws `UnsupportedApproval`, failing the turn. Present
   since upstream `0.113.0` (built-in tool introduced;
   [release](https://github.com/openai/codex/releases/tag/rust-v0.113.0)),
   so this is a pre-existing gap, not a regression from any reviewed
   release. Suggested: add a `KIND_PERMISSIONS` approval kind (subject from
   `reason`/`grantRoot`) or document the limitation; validate with
   `AppServerTurnTransportTest` using a recorded `requestPermissions`
   payload. Related: experimental `item/tool/call` (dynamic tools) and
   `tool/requestUserInput` (app-connector approvals) would take the same
   throw path if ever received — same handling decision covers all three.
2. **`fork()` on the app-server transport does not fork.**
   `AppServerTurnTransport::stream()`
   (`src/Transport/AppServerTurnTransport.php:102-104`) calls `threadStart`
   for every non-`Resume` mode, so `Thread::fork()` (`src/Thread.php:107-124`)
   over an interactive transport silently starts an **empty** thread instead
   of branching history, while upstream documents `thread/fork{threadId,
   lastTurnId?, ephemeral?}` returning the forked id plus `thread/started`
   ([docs](https://github.com/openai/codex/releases/tag/rust-v0.153.4)
   via app-server guide). Suggested: add an `AppServerClient::threadFork()`
   used when `RunMode::Fork`, or throw to make the limitation loud;
   validate against the documented fork request/response example.
3. **No `turn/steer` support.** Upstream offers `turn/steer` to append input
   to an in-flight turn ([docs](https://learn.chatgpt.com/docs/app-server)).
   The package has no API for it (optional feature; no compatibility impact).

## Required actions

None. No reviewed stable release breaks the package's exec transport,
parsing, usage accounting, error handling, or account/quota/credit reads.

## Unresolved uncertainties

- **Compatibility baseline** is undeclared (see above); "compatible with
  0.153.4" is verified, but the oldest CLI the package supports is unknown.
  Old CLI variants (e.g. pre-`fork` subcommand, different `thread_id`
  casing) are handled defensively in a few places
  (`JsonlEventParser.php:52` accepts `thread_id`/`threadId`) without stating
  since when.
- Releases older than the 1000-result API window were not enumerated; all
  findings above were dated within the visible window, so this does not
  affect the verdict.
- `request_user_input_async` (0.153.0 notes) has no stable wire contract
  found in the fetched protocol sources; assessed as inert for the package
  because only `thread.id` is read from thread responses. If a future stable
  release turns it into a mandatory server→client request on turns, finding 1
  (recommended actions) becomes required.
- Interactive-path findings were verified against protocol sources and docs,
  not by live runs (no authenticated `codex app-server` session was opened;
  per scope, no software was installed or upgraded).

## Changes since previous report

None — this is the first report (no prior `.research/CODEX-RELEASE-REPORT.md`
existed). Next runs should compare new stable releases, changed notes for
`0.147.0`–`0.153.4`, and package changes against the findings above, and
reassess compatibility even if no newer stable release exists.
