# Codex CLI Release Compatibility Report — `develate/codex-cli-php`

## Verdict: No update needed

No new required PHP compatibility fix is identified in the **assessed working
tree** for the consumed stable-release interfaces through **0.165.0**. Two new
stable releases, **0.164.0 and 0.165.0**, have been reviewed since the saved
0.163.0 checkpoint. Exec arguments and JSONL remain unchanged. Consumed quota,
credit, approval, permission, and thread/turn contracts remain usable. New account
and error variants, image-reference alternatives, optional APIs, runtime fixes,
and changed defaults are assessed below; a newer CLI alone does not require a
PHP package update.

The prior interactive item, raw-payload, interruption, fork, steering, usage,
permission, and child-thread fixes remain present. Package assessment inputs are
byte-identical to the October 19 report, and all existing tests pass again.
These fixes remain uncommitted here; no published artifact was verified. This
verdict does not certify a released 1.7.0 artifact. Consumer quota admission,
richer typed features, and failed-turn usage aggregation remain watch items.
No supported CLI minimum or range is established; reviewing a release does not
establish support for it.

## Assessment metadata

- Date of last substantive assessment: **2026-10-27** (Europe/Vienna).
- Package repository: `code-cli/codex-cli-php`, HEAD
  **`8bea1a39de5be49dbae28cc1077d7ad2b0056323`**; unchanged since the previous
  assessment. Relevant working-tree state remains modified `CHANGELOG.md`,
  `README.md`, `src/AppServer/AppServerClient.php`,
  `src/AppServer/NotificationTranslator.php`, `src/Event/ThreadTokenUsageUpdated.php`,
  `src/Event/TurnFailed.php`, `src/Parser/JsonlEventParser.php`, `src/Thread.php`,
  `src/Transport/AppServerTurnTransport.php`, `tests/Parser/JsonlEventParserTest.php`,
  and `tests/Transport/AppServerTurnTransportTest.php`; untracked
  `src/Event/AccountRateLimitsUpdated.php`, `src/Transport/SteerableTransport.php`,
  `tests/AppServer/NotificationTranslatorTest.php`,
  `tests/AppServer/RateLimitsUpdatedTest.php`, and `tests/Transport/SteeringTest.php`.
  The report was already modified before this review. HEAD alone does not
  represent the assessed implementation. Only this report was edited.
- Relevant package changes since the previous report: **none**. Source,
  documentation, tests, inline fixtures, dependency declarations, and test
  configuration match the saved fingerprint. Existing fixes and unresolved
  findings were reassessed against the latest stable interfaces.
- Relevant history inspected: initial SDK `1ab829f`, reset credits `93c8483`,
  interactive approvals `f492150`, MCP elicitation `6fddfcc`, initial research
  `e7a0e3d`, experimental realtime `2925d5c`, permissions `28a0a29`, lifecycle/
  usage events `4f307c7`, and environment/collaboration support `8bea1a3`.
  Initial/current requirements, version probes, test version fixtures and tags
  were checked for baseline evidence. No applicable `AGENTS.md` was found in the
  package, its enclosing directories, or its source/test/research subdirectories.
  The separate `code-client/AGENTS.md` was read before the consumer reassessment.
- Fresh validation: `php vendor/bin/phpunit --do-not-cache-result --no-coverage`
  passed: **144 tests, 717 assertions**, PHP 8.5.3 / PHPUnit 11.5.56.
  Read-only PHP payload probes also confirmed exact raw preservation for both
  inline and file-ID image items, `modelNotFound` failure details, and explicit
  `ordinaryUsageAllowed=false`. These probes did not add test files.
- Installed native CLI **0.165.0** version and `exec`, `exec resume`, `exec fork`,
  and `app-server` help probes passed, retaining used image/schema arguments
  and `--stdio`. The PATH JavaScript launcher failed first with
  `env: node: Bad CPU type in executable`; running its already-installed ARM64
  native executable directly succeeded. Help printed a PATH-alias sandbox
  warning without failing. This environment/launcher limitation is separate
  from PHP wire compatibility; the default PATH invocation was not repaired or
  certified. No software was installed or upgraded.
- Package assessment-input fingerprint: SHA-256
  **`ef996e790361fce3ac230c4957b3fd9dce2500f52de86f9fc966aaadaf39d48f`**,
  **unchanged**. Computed over 102 files: all `src/**/*.php`, `tests/**/*.php`,
  `README.md`, `CHANGELOG.md`, `composer.json`, `composer.lock`, and
  `phpunit.xml.dist`; sorted by relative POSIX path, hashing
  `path UTF-8 + NUL + file bytes + NUL` for each. This excludes the report and
  unrelated repository files.
- The retained consumer quota finding was reassessed read-only in `code-client`
  HEAD **`1564f52aec7b28c35a11889bf8ad189548ef623a`**, specifically
  `app/Agent/QuotaService.php`, `app/Agent/Providers/CodexAdapter.php`, and
  `app/Agent/CodexEventNormalizer.php`. Those files are clean and unchanged from
  the previous client revision `ca3082294cf293d081253b311161c28d8909c3ba`.
  Sparse-update merging remains present; ordinary-usage admission still relies
  on percentages. Client tests were not rerun; this is not a package baseline.
- Tests use inline fixtures and temporary fake executables. No authenticated
  turn/account query, credit redemption, live media session, commit, push, or
  publication was performed. Test success is not upstream integration certification.

## Version distinctions and compatibility baseline

| Meaning | Evidence and conclusion |
| --- | --- |
| PHP package version | **1.7.0** is the latest numbered `CHANGELOG.md` entry and the `code-client/composer.json` path-package alias. The package's own `composer.json` has no version field, and this checkout has no version tag. Later dated/uncommitted entries do not establish a new published version. |
| Locally installed CLI | **0.165.0**, freshly reported by the installed native executable `/Users/thorsten/.nvm/versions/node/v22.22.2/lib/node_modules/@openai/codex/node_modules/@openai/codex-darwin-arm64/vendor/aarch64-apple-darwin/bin/codex --version`, matching its npm package manifest. The PATH launcher failed because Node could not execute; native version/help probes succeeded. Previous local 0.163.0 observation superseded; no upgrade performed. |
| Latest reviewed stable upstream | **0.165.0**, [`rust-v0.165.0`](https://github.com/openai/codex/releases/tag/rust-v0.165.0), published **2026-10-27T20:09:38Z**. |
| Supported compatibility baseline | **Unclear; no minimum or supported range is declared.** Current and initial README requirements ask for a working authenticated CLI without a version. `UnsupportedCodexVersion` remains unused; `Codex::version()` parses/caches a version but does not enforce support. `supportsInteractiveTurns()` checks transport configuration, not CLI capabilities. |

`ThreadTest`'s `0.150.1` and the usage fixture's `0.157.1` are fake-transport
values, not upstream integration certifications. Earlier 0.153.4, 0.157.1, and
0.160.0 assessments are review checkpoints, not compatibility baselines.
Because a supported baseline cannot be established from repository evidence,
this run assesses current code against the **latest stable interfaces**, retains
the historical reviewed range, and makes no blanket support promise for it.
The `clientInfo.version` value **0.1.0** in `AppServerClient::initialize()` is a
separate handshake identifier, not the PHP package or CLI version.

The latest reviewed tag resolves to
[commit `360319d8dee62d87e2d59fc0c21805d79519cfed`](https://github.com/openai/codex/commit/360319d8dee62d87e2d59fc0c21805d79519cfed).
Baseline evidence is in [README requirements](../README.md),
[`Codex::version()` / `supportsInteractiveTurns()`](../src/Codex.php),
[the unused exception](../src/Exception/UnsupportedCodexVersion.php), tests, and
initial commit `1ab829f`; none declares a tested CLI range. Experimental realtime
remains separately conditional on installed CLI/backend capabilities.

## Fresh upstream coverage and reviewed release range

Fetched OpenAI's public [release repository](https://github.com/openai/codex/releases)
and the unauthenticated GitHub Releases API afresh with `Cache-Control: no-cache`:
[page 1](https://api.github.com/repos/openai/codex/releases?per_page=100&page=1),
[page 2](https://api.github.com/repos/openai/codex/releases?per_page=100&page=2), and
[page 3](https://api.github.com/repos/openai/codex/releases?per_page=100&page=3).
All **300 records** were enumerated, reaching stable 0.146.0, older than the saved
0.147.0 boundary. Removed drafts and GitHub prereleases, normalized `rust-v` and
`v` prefixes, accepted only anchored plain numeric `major.minor.patch` CLI
versions, and sorted integer tuples. Alpha, beta, rc, preview, nightly, dev, and
other suffixed/non-CLI versions were excluded regardless of GitHub flags or
public visibility. Listing order was not used to establish stability or recency.

- Previous reviewed inventory: **0.147.0–0.163.0, 32 stable releases**.
- Newly reviewed: **0.164.0 and 0.165.0**, two stable releases.
- All 32 previous releases remain stable/public in the fetched inventory. Their
  exact note-body SHA-256, `published_at`, and `updated_at` values are unchanged.
- Retained reviewed range: **0.147.0–0.165.0, 34 stable releases**. Exact note
  hashes and timestamps for all 34 releases are saved below. The fetched window
  contains 36 plain stable versions in total; older 0.146.0/0.146.1 records do
  not extend the reviewed range.
- Complete, untruncated upstream Git trees were compared at **0.163.0, 0.164.0,
  and 0.165.0** (10,157 / 10,444 / 10,519 entries). **195 downloaded tagged source
  files** across these tags were checked against Git blob hashes. Both new full
  release notes and relevant tagged CLI, JSONL, protocol, extension-item and
  runtime changes were inspected. Fresh official non-interactive, app-server,
  and command documentation was also read. Each retained package finding was
  reassessed. This is static interface review, not runtime certification of
  intermediate versions. Earlier pre-0.147 research remains historical context.

| Newly reviewed stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0) | 2026-10-23T04:48:10Z | Saved session settings/compressed-history restoration, config-override fixes, compaction/usage corrections, MCP authentication and sandbox/runtime fixes. Exec arguments/JSONL and consumed quota/credit contracts are unchanged. File-ID image results and optional metadata/API additions are assessed below. |
| [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) | 2026-10-27T20:09:38Z | Optional Vertex AI account/provider and attached forks; reasoning-effort and fresh-profile memory defaults; terminal model-not-found errors; agent-message interruption and MCP cancellation fixes. Existing wrapper requests remain valid; new payloads are retained. |

Stable release retained from the October 19 review:

| Stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.163.0](https://github.com/openai/codex/releases/tag/rust-v0.163.0) | 2026-10-15T21:30:38Z | Default-enabled CLI Daybreak controls/API-key program forwarding, Code Mode interruption, cold-resume permission restoration, incomplete-response error/usage accounting, and runtime fixes. Previous checkpoint; failed-turn usage and optional API findings remain below. |

Stable releases retained from the October 11 review:

| Stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.160.1](https://github.com/openai/codex/releases/tag/rust-v0.160.1) | 2026-10-05T18:29:37Z | Windows remote stdio MCP environment fix; CLI runtime change. No consumed argument or payload change. |
| [0.161.0](https://github.com/openai/codex/releases/tag/rust-v0.161.0) | 2026-10-07T15:58:45Z | Catalog/default-model, permissions, resume/history, retry, Bedrock, voice, and MCP improvements. Optional experimental `--cyber-access-program` does not affect commands that omit it. Daybreak controls/routing require opt-in. |
| [0.162.0](https://github.com/openai/codex/releases/tag/rust-v0.162.0) | 2026-10-08T18:55:59Z | Worktree/TUI/provider/Code Mode features plus sandbox, CRLF, retry, and Windows fixes. Command-output consolidation retains consumed aggregate fields; partial-answer phase, turn lineage, and usage changes are assessed below. |
| [0.162.1](https://github.com/openai/codex/releases/tag/rust-v0.162.1) | 2026-10-09T19:44:51Z | Multiline asynchronous-question TUI rendering and background-server feature-compatibility startup fixes; CLI runtime improvements. No consumed wire-contract removal found. |

The earlier stable-release observations remain relevant historical context:

| Stable release retained from the October 3 review | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.158.0](https://github.com/openai/codex/releases/tag/rust-v0.158.0) | 2026-09-28T05:07:23Z | Terminal-input approval becomes a default for elevated commands; command completion now retains early output and launch failures. Image-generation background/file-backed-image improvements, sandbox fixes, and optional executor WebSocket authentication do not require new PHP wire fields. |
| [0.159.0](https://github.com/openai/codex/releases/tag/rust-v0.159.0) | 2026-09-29T08:05:42Z | Optional `instant_interrupt`, steering continuation fixes, item-history anchors, sandbox denial preservation, and default `.aws` protections. Tagged code also adds opt-in structured circuit-breaker errors; see interruption handling below. |
| [0.159.1](https://github.com/openai/codex/releases/tag/rust-v0.159.1) | 2026-09-29T20:32:34Z | Bundled default model changes to GPT-6.1 Sol. The package accepts model strings and can explicitly select one; no PHP API change is required. |
| [0.159.2](https://github.com/openai/codex/releases/tag/rust-v0.159.2) | 2026-09-29T23:57:16Z | Windows console-suppression fix; CLI-only upgrade. |
| [0.159.3](https://github.com/openai/codex/releases/tag/rust-v0.159.3) | 2026-09-30T22:57:34Z | Optional local account-security reminders; CLI UI behavior. |
| [0.160.0](https://github.com/openai/codex/releases/tag/rust-v0.160.0) | 2026-10-01T20:19:13Z | Provider defaults/catalogs, TUI resume/fork, projectless sessions, queued-input reconnection, environment preparation, SQLite, and Windows fixes. Prior interface checkpoint; the PHP fixes are retained below. |

## Compatibility findings

### 1. Exec commands, JSONL, images, structured output, and usage: no new required fix

`ExecTransport::command()` still constructs valid start/resume/fork commands with
`--json`, model, local images, and output schema. Start-only cwd, sandbox, color,
and writable-root flags are omitted on resume/fork, which reuse persisted state.
The [exec CLI arguments](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/exec/src/cli.rs),
[JSONL event/item declarations](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/exec/src/exec_events.rs),
and [JSONL emitter](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/exec/src/event_processor_with_jsonl_output.rs)
have identical Git blobs at **0.163.0, 0.164.0 and 0.165.0**. `Usage::fromArray()`
and `TurnResultBuilder` consume the retained completion counters. Malformed JSONL
and process failures keep their typed exception paths. Native 0.165.0 help
independently confirms emitted argument forms without an authenticated run.

Unknown exec events/items become `UnknownEvent`; additional fields survive in
`raw()`. Existing parser tests exercise this tolerance. Optional web-search
results and extra item capabilities therefore do not by themselves break parsing.
Optional `--worktree`, previously reviewed in
[0.154.0](https://github.com/openai/codex/releases/tag/rust-v0.154.0), and
`--cyber-access-program` remain unexposed. Their availability does not invalidate
commands that omit them.

Callers omitting `model` inherit CLI/provider catalog choices. The CLI catalog
now offers Terra; explicit model strings remain accepted. Explicit sandbox
modes and local-image inputs retain their wire forms. Provider/default changes
and image-generation **output** references are assessed in findings 10–11;
file-ID results do not change the package's local-image **input** argument.
The fresh [official non-interactive documentation](https://learn.chatgpt.com/docs/non-interactive-mode)
corroborates JSONL and output-schema behavior; tagged code controls the
stable-version conclusions.

### 2. App-server launch, RPCs, quotas, and credits: consumed wire contracts retained

`JsonRpcConnection::connect()` launches `codex app-server --stdio`. This flag
remains accepted by the [0.165.0 CLI](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/cli/src/main.rs),
equivalent to `--listen stdio://`; native help and the
[official command reference](https://learn.chatgpt.com/docs/developer-commands)
confirm it. `AppServerClient` initializes and sends account, quota/reset-credit,
thread start/resume/fork, and turn start/steer/interrupt methods retained by the
[tagged RPC registry](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/common.rs).
Initialize fields and consumed thread start/resume settings are retained.

The [account/quota/credit schema](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/account.rs)
changes only by adding the `gcp` account variant relative to 0.163.0; **quota,
credits, reset-credit parameters and all four reset-credit outcomes are unchanged**.
`QuotaMapper` still maps generic windows, additional buckets, credits, and nullable
reset-credit details, preserving the response in `Quota::raw`. The explicit-false
ordinary-usage permission watch item remains unresolved (finding 7).
`AppServerTransport::account()` accepts the provider type as a string, so the
new `gcp` variant does not encounter a closed PHP enum. Backend/account access
and reset-credit consumption were not exercised.

Fork adds default-false experimental controls, including attached forks, which
this package omits; ordinary forks remain valid (finding 11). New sampling,
memory-read, diagnostic-export and metadata APIs are optional and are not called.
No new mandatory server-to-client request method was found. Unknown notifications
remain `UnknownEvent` with raw payloads. Additional account/model/history metadata
and optional experimental APIs do not establish required PHP fixes.

### 3. Resolved: known interactive item translations and original payloads

The stable app-server
[`CommandExecutionStatus`](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L1074-L1082)
uses `inProgress`. `NotificationTranslator::item()` now translates it to the
common `in_progress` value, so the parser produces `CommandStatus::InProgress`.
Other known statuses retain their meanings and future statuses remain `Unknown`.
Stable
[`FileUpdateChange` / `PatchChangeKind`](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L1137-L1154)
uses object kinds such as `{type: "update"}`. `changes()` now reads their `type`,
while retaining older string kinds and path-keyed changes. Regression fixtures
cover add/delete/update with a rename, a future kind, and original diffs.

`NotificationTranslator::translate()` attaches the complete original notification
for every translated event. `JsonlEventParser` passes it to all typed and unknown
events, retaining identity, metadata, diffs, structured errors, and envelope
fields in `raw()`. Exec JSONL parsing explicitly retains its entire original
event, even when a future field is itself named `raw`. The new
`NotificationTranslatorTest` and parser regression exercise these distinctions.
These correct pre-existing defects without rejecting unknown additive fields.

### 4. Resolved: interrupted completion and structured error preservation

`NotificationTranslator::turnCompleted()` now emits `TurnFailed` for an
interrupted completion, including `error: null`, instead of emitting successful
`TurnCompleted`. `TurnFailed::status` preserves the upstream status and defaults
to `failed` for existing exec failures. The interrupted event remains visible in
`TurnResult::errors()`; its original completion/error payload remains in `raw()`.
The README documents that the synthetic app-server process exit 0 describes the
transport and must not be equated with model success.

The stable [turn statuses](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/turn.rs#L30-L39)
distinguish completed, interrupted, failed, and in-progress. The
[0.159.0 app-server documentation](https://github.com/openai/codex/blob/rust-v0.159.0/codex-rs/app-server/README.md#L1-L8)
adds optional `auto_review.circuit_break_action = "strict"`: interruption includes
`tooManyDenials`; the default leaves the error unset and neither emits a separate
error event. Both cases now retain non-success status and original error details.
New [error variants](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/shared.rs#L70-L89)
remain additive rather than unsupported protocol versions. JSON-RPC exceptions
continue to retain RPC code/data.

Regression coverage includes failed completion, interruption with and without
structured errors, and `ApprovalDecision::Cancel` followed by interruption. The
stream terminates and avoids a redundant interrupt request. The
[official app-server documentation](https://developers.openai.com/codex/app-server)
corroborates the terminal-status distinction. No authenticated cancellation was
performed.

### 5. Resolved: interactive forks call `thread/fork`

`AppServerTurnTransport::stream()` now dispatches `RunMode::Fork` to the new
`AppServerClient::threadFork()` with the parent thread ID and thread configuration.
That method uses the stable
[`thread/fork` request](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/thread.rs#L539-L632),
requests metadata without full turn hydration, and validates the returned child
ID. Missing, empty, or reused parent IDs in the response fail visibly instead of
silently using an unrelated or original thread. Missing input parent IDs are
rejected before starting the server.

`AppServerTurnTransportTest` now exercises `Thread::fork()` through the actual
transport/client path: it verifies the parent-ID fork request, returned child ID,
subsequent resume routing, configuration, and absence of `thread/start`. It also
covers invalid child IDs. Upstream performs the history copy; authenticated
history inheritance remains a live-validation limit. Exec fork construction and
the existing fake-transport `ThreadTest` retain their behavior.

### 6. Steering and child-thread handling: prior gap resolved/new local behavior assessed

The previous optional `turn/steer` feature is now implemented in the **working
tree** by `Thread::steer()`, `SteerableTransport`,
`AppServerTurnTransport::steer()` / `onTick()`, and `AppServerClient::turnSteer()`.
Its `threadId`, `expectedTurnId`, text/local-image input, and acknowledged
`turnId` match the unchanged
[stable steering schema](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/turn.rs#L289-L323).
`SteeringTest` covers idle/exec rejection, mismatch/error responses, silent
reasoning, images, and completion buffered before ticking. No claim is made
about a delivered PHP release or live steering latency. The
[0.159.0](https://github.com/openai/codex/releases/tag/rust-v0.159.0)
`instant_interrupt` feature does not require a different wire contract. It was
already default-enabled at the saved 0.162.1 checkpoint; see finding 10 for the
corrected default assessment.

The September 28 `belongsToAnotherThread()` change prevents child lifecycle
and recognized child item notifications from ending or speaking for the root
turn, while keeping collaboration/usage/reroute facts visible. Its dedicated
fake-server test passes. This corrects local root-turn handling; it is not an
upstream protocol break or proof of every multi-agent workflow.

### 7. Earlier fixes and quota watch items retained

- **Interactive usage fix remains present.** Stable `Turn` has no usage field;
  `AppServerTurnTransport::translate()` derives root-turn counters from token
  notification totals and last-response usage, excluding prior turns, repeats,
  out-of-order updates, and child counters. Its test passes. Sources:
  [`Turn`](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/thread_data.rs#L386-L409)
  and [token notification schema](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/thread.rs#L1875-L1965).
  Missing initial token updates still prevent complete reconstruction.
- **Sparse quota consumer fix remains present.** The package's typed
  `AccountRateLimitsUpdated` preserves raw sparse notifications; absent sections
  are null. The [upstream merge requirement](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/account.rs#L650-L660)
  still applies. Current `code-client/app/Agent/QuotaService::persist(partial: true)`
  retains absent sections and the last full-read timestamp. That service remains
  committed in the inspected client HEAD. The package does not guarantee complete
  snapshots from rolling events. This run reconfirmed the merge behavior in the
  current client checkout.
- **`ordinaryUsageAllowed` consumer review remains unresolved.** Introduced in
  [0.154.0](https://github.com/openai/codex/releases/tag/rust-v0.154.0), this
  [nullable backend permission](https://github.com/openai/codex/blob/rust-v0.160.0/codex-rs/app-server-protocol/src/protocol/v2/account.rs#L327-L345)
  is kept only in `Quota::raw`. The inspected client still gates availability
  with positive percentages in `QuotaService::windowsAvailable()`, and its
  normalizer does not expose this signal. Review explicit `false` before
  admitting/retrying a turn; absent/null is not an affirmative permission.
  This is a consumer recommendation, not a new required PHP response-field fix.

### 8. Approvals, sandbox defaults, and optional features

The previous missing-permission-handler finding stays **resolved** by commit
`28a0a29` and the passing `PermissionRequest` / `PermissionResponse` tests. The
[stable permission schema](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/permissions.rs)
is byte-identical across 0.163.0–0.165.0 (and the prior 0.162.1 check). Interactive thread setup explicitly uses `on-request`, or `never`
for full access. Command/file approval decisions and MCP elicitation responses
still match the consumed contracts; unknown server requests fail visibly.

[0.158.0](https://github.com/openai/codex/releases/tag/rust-v0.158.0) changes the
terminal-input approval default. Its
[feature default](https://github.com/openai/codex/blob/rust-v0.158.0/codex-rs/features/src/lib.rs)
and [stdin review implementation](https://github.com/openai/codex/blob/rust-v0.158.0/codex-rs/core/src/unified_exec/stdin_approval.rs)
reuse command approval requests with `kind: "writeStdin"` and a distinct
`approvalId`. The package already retains params in `ApprovalRequest::payload`
and that ID in `callId`. Validate repeated callbacks and ensure consumers show
the actual action/reason. More frequent prompts are a behavior/default change,
not a new unhandled RPC method. Sandbox-denial/protected-directory corrections
are CLI behavior; a denied write is not evidence of a PHP serialization break.

The [experimental realtime schema](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/realtime.rs)
adds optional `voiceSessionId` at 0.164.0; `RealtimeStartRequest` may omit it.
`RealtimeSession`, start/audio/text values and WebRTC/existing-call transports
retain consumed wire forms. Optional startup/history/handoff/voice-correlation
controls are not required. Backend availability and live media remain unverified.

The async-input watch item remains optional: stable agent-message
[question metadata](https://github.com/openai/codex/blob/rust-v0.157.1/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L251-L263)
already existed at the prior tag and is now preserved in original raw
notifications; typed question presentation remains optional.
The same tagged item schema represents experimental plans as `text`, whereas
`NotificationTranslator::item()` reads `items` and produces an empty todo list.
Plan text is now available in `raw()` as well. Typed plan/question presentation
remains an optional feature gap; no richer typed support is claimed. Add typed
presentation/request handlers only for an
intended workflow. No mandatory new `request_user_input_async` server-request
contract was established. Optional file-ID images, worktrees, item-history
paging, account routing, and model metadata do not require PHP changes merely
because upstream exposes them.

### 9. Retained stable metadata and internal changes: no required fix established

- **Partial-answer messages.** Stable
  [`MessagePhase`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/protocol/src/models.rs#L938-L953)
  adds `partial_answer`; app-server
  [`AgentMessage`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L250-L263)
  keeps `text` and optional semantic `phase`. `NotificationTranslator::item()`
  still reads text, and the complete semantic phase survives in `raw()`.
  PHP `AgentMessage::phase` is the item lifecycle (`started`/`completed`), not
  that semantic phase. The unchanged exec emitter omits semantic phase and
  [selects the latest agent message](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/exec/src/event_processor_with_jsonl_output.rs#L387-L403),
  consistent with `TurnResultBuilder::build()`. No new completion or response
  failure is demonstrated. Typed semantic phases and combining/displaying
  partial answers remain optional product decisions. Current
  [official app-server documentation](https://learn.chatgpt.com/docs/app-server)
  lists only commentary/final-answer phases; the newer stable-tag enum is the
  evidence for this finding.
- **Turn lineage and sub-agent details.**
  [`TurnStartParams`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/turn.rs#L179-L194)
  adds optional `parentTurnId`/`rootTurnId`; direct user work may omit them.
  [`Turn`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/thread_data.rs#L386-L404)
  adds nullable `rootTurnId`, while
  [`SubAgentActivity`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L393-L411)
  adds model/reasoning metadata. Current requests remain valid. Raw notifications
  retain new fields, and unknown sub-agent items remain observable. Existing
  child-thread filtering and root token-counter handling still apply.
- **Command output and usage.** Core
  [`ExecCommandEndEvent`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/protocol/src/protocol.rs#L3630-L3639)
  removes legacy separate stdout/stderr, retaining aggregate output. Public
  [`CommandExecution`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/item.rs#L309-L326)
  still uses `aggregatedOutput`; exec still uses `aggregated_output`.
  `NotificationTranslator` and `JsonlEventParser` already consume those fields.
  Root token-notification fields and the public
  [thread-usage schema](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/thread_usage.rs)
  are unchanged. New optional
  [backend native-dollar totals](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/backend-client/src/client/thread_usage.rs#L13-L37)
  are separate from completion tokens; this package does not call thread-usage
  read RPCs. Their presence does not require changing `Usage` or quota mapping.
- **Errors and unconsumed APIs.**
  [`CodexErrorInfo`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/shared.rs#L118-L146)
  gains tolerant upstream decoding of unknown string/object variants; its
  fallback still serializes as `other`. Optional misalignment `reviewTarget`
  remains in original raw errors. The PHP parser does not reject those fields
  or use a closed error enum. The actual `namespaceTools` removal is on
  [`ModelProviderCapabilitiesReadResponse`](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/app-server-protocol/src/protocol/v2/model.rs#L43-L49),
  an API this package does not consume. Environment-request/selection changes
  in the [core protocol](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/protocol/src/protocol.rs)
  are internal, with consumed thread/turn settings retained. Prediction, goal
  provenance, and environment controls are optional APIs, not new required
  calls. No concrete package call path depends on the removed field.

### 10. Previously reviewed 0.163.0 behavior and optional APIs

- **Steering and interruption defaults.** The release notes highlight instant
  steering and interruption of active Code Mode cells/nested tools. Tagged
  [feature defaults](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/features/src/lib.rs#L1195-L1212)
  make Code Mode interruption stable and default-enabled. Correction to the
  prior optional-feature wording: `instant_interrupt` was
  [already default-enabled at 0.162.1](https://github.com/openai/codex/blob/rust-v0.162.1/codex-rs/features/src/lib.rs#L1158-L1163).
  `turn/steer` request/response fields remain unchanged. Existing local
  `Thread::steer()`, terminal-status handling, and buffered-completion tests
  remain relevant; validate late steering rejection and stopping nested tools
  in a live isolated-thread workflow. No new PHP method is required.
- **Daybreak and provider behavior.** The
  [CLI feature gates](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/features/src/lib.rs#L1411-L1422)
  now enable CLI Daybreak controls and API-key Cyber program forwarding by
  default. This does not mean every wrapper call selects Daybreak: the
  [configuration preference](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/core/src/config/mod.rs#L4406-L4411)
  still defaults to false, and
  [`exec::daybreak::program_for_turn`](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/exec/src/daybreak.rs)
  checks provider/account/catalog eligibility, selecting a standard program
  when appropriate or preserving automatic treatment when a catalog entry is
  unavailable. Explicit Daybreak/Cyber selection remains optional PHP API
  support. The wrapper inherits CLI configuration; no newly mandatory argument
  or entitlement claim is established.
- **Cold-resume permissions and runtime fixes.** The
  [thread resume implementation](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server/src/request_processors/thread_processor.rs#L4040-L4167)
  restores stored permissions/network grants while respecting explicit
  overrides and managed policy. `ExecTransport::command()` already omits
  start-only permission flags on resume/fork; interactive
  `AppServerTurnTransport::threadParams()` explicitly supplies sandbox and
  approval settings. Check persisted grants without overrides separately from
  explicit overrides. Network-proxy enforcement, standard-shell execution
  replacing patched zsh, and local/remote output-draining fixes ship in the CLI.
  The repeated-diff fix concerns human exec output; the JSONL emitter is
  unchanged. These are runtime changes, not PHP serialization fixes.
- **Incomplete responses and spending.** The
  [terminal error type and retry classification](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/protocol/src/error.rs)
  and [sampling error path](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/core/src/session/turn.rs#L2768-L2781)
  now stop retrying ordinary incomplete responses and account for any reported
  token usage. They use existing error/token notifications; the public error
  classification falls back to `other`. `NotificationTranslator::turnCompleted()`
  and `TurnFailed` already preserve failure status and original errors.
  **Accounting limit:** `TurnResultBuilder::add()` only installs aggregate
  usage from `TurnCompleted`, and the interactive transport reconstructs usage
  only for successful completion. Failed/interrupted `TurnResult::usage` can
  therefore remain zero even when typed `ThreadTokenUsageUpdated` events
  contain spending. This is a completeness watch item for consumers needing
  failed-turn cost accounting, not a new wire incompatibility. Validate reported
  usage followed by failure/interruption and inspect events rather than treating
  a zero result aggregate as proof of no spending. Upstream's
  [spending-only accounting](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/core/src/session/mod.rs#L5059-L5073)
  increments cumulative totals while retaining the stored sampling-context
  `last` value. Per-turn accounting therefore needs total deltas against a known
  root-thread baseline; the final `last` snapshot alone is insufficient.
- **Optional schemas and internal client changes.** The tagged
  [thread schema](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server-protocol/src/protocol/v2/thread.rs)
  adds default-false `experimentalPredictionMode`, list exclusions and
  experimental read state; the
  [RPC registry](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server-protocol/src/protocol/common.rs)
  adds experimental item lookup/read-state methods and a notification.
  [Tool outputs](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server-protocol/src/protocol/v2/turn.rs#L151-L163)
  gain default-false `retain`, and
  [environment setup](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server-protocol/src/protocol/v2/environment.rs)
  gains optional `websocketRequestId`. None is required by this package's
  calls. Unknown notifications and extra fields remain observable through raw
  payloads. New realtime v3
  [voices](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/protocol/src/protocol.rs#L256-L403)
  fit `RealtimeStartRequest`'s string voice/version fields; the
  [start/stop RPC implementation](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server/src/request_processors/turn_processor.rs#L1212-L1407)
  retains the package's consumed operations despite new core attachment/session
  behavior. MCP
  [elicitation abandonment](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/protocol/src/approvals.rs#L451-L456)
  is an internal event, and Rust
  [connection-bound reply handles](https://github.com/openai/codex/blob/rust-v0.163.0/codex-rs/app-server-client/src/server_request.rs)
  are client internals. Neither establishes a new mandatory JSON-RPC request
  that the PHP connection must answer.

### 11. New 0.164.0–0.165.0 changes and focused impact

- **Resume/fork persistence and overrides.**
  [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0) fixes lookup
  of compressed histories and restores saved model/provider/reasoning settings.
  The [latest thread processor](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server/src/request_processors/thread_processor.rs)
  preserves explicit overrides and restores saved settings when absent. PHP
  `threadParams()` still explicitly sends sandbox/approval settings and any
  selected model; `ExecTransport` omits start-only runtime flags on resume/fork.
  The new [fork fields](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/thread.rs)
  `attachToSource` and `experimentalDisablePredictionMcp` both default false.
  Omitted/false attachment explicitly creates an ordinary fork; prediction MCP
  controls apply only to prediction mode. Existing `AppServerClient::threadFork()`
  stays valid. Attached forks, experimental samples/cancellation, prompt-cache
  controls and metadata patches are optional feature support. Validate saved
  history/settings with omitted versus explicit model/sandbox overrides.
- **Image-generation references.** The
  [image extension item](https://github.com/openai/codex/blob/rust-v0.164.0/codex-rs/ext/items/src/image_generation.rs)
  adds `GeneratedImageRef`, flattened/untagged into **either `fileId` or legacy
  `result`**, plus optional `inputImages` path/hash metadata. The Rust `image`
  member is not a nested wire field. A raw caller that assumes `result` is always
  present must handle file IDs. `NotificationTranslator` does not type
  `imageGeneration`; it returns `UnknownEvent` and preserves the full original
  item. Both alternatives passed read-only PHP preservation probes. This is an
  optional image-consumer/reference feature gap, not a required wrapper parser
  change. Local input images, structured-output schema requests and exec JSONL
  remain unchanged. Web-search/sleep extension-item blobs are also unchanged.
- **Terminal model-not-found errors.**
  [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) stops retrying
  provider model-not-found failures and preserves the provider message. The
  [error definition](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/protocol/src/error.rs)
  and [public error classification](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/shared.rs)
  add `modelNotFound`; existing failure events/statuses remain. `TurnFailed`,
  `ErrorEvent` and JSON-RPC exceptions retain their error/raw data and do not use
  a closed PHP error enum. The new failure probe passed. Consumers may classify
  this terminal error explicitly to avoid their own pointless retries; no new
  required PHP wire field or argument is established.
- **Usage and compaction.** The
  [0.164.0 token-usage correction](https://github.com/openai/codex/pull/55991/files)
  avoids counting retained reasoning twice for Responses Lite context estimates
  ([tagged accounting code](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/core/src/session/mod.rs#L5227-L5330)).
  It does not remove or rename public completion counters or token notifications;
  the [public thread-usage schema](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server-protocol/src/protocol/v2/thread_usage.rs)
  remains identical across the three compared tags. `Usage` and root-thread
  reconstruction remain applicable. Compaction fallback fixes ship in the CLI.
  The failed/interrupted aggregate limit in finding 10 persists; corrected CLI
  estimates do not resolve it. Validate repeated/out-of-order root counters,
  child exclusion and reported usage followed by failure/interruption.
- **Provider and default behavior.**
  [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) adds Vertex AI
  (`model_provider="gcp"`) and Terra catalog entries. The wrapper passes model
  strings and inherits provider configuration; configuring a new provider or
  adding typed account details is optional. The
  [feature defaults](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/features/src/lib.rs)
  enable reasoning-effort overrides for supported models. PHP has no typed effort
  argument to update. Fresh-profile memory v2 selection in
  [configuration loading](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/core/src/config/mod.rs)
  is conditional on memory enablement, no explicit version, no dual-write opt-out,
  and demonstrably absent v1 storage. It preserves explicit choices. These
  behavior changes do not make new request fields mandatory. Incremental tools
  and coordinated MCP OAuth refresh are also default-enabled relative to
  0.163.0. Worktree and TUI voice compatibility gates are retired while their
  functionality remains available. The wrapper does not depend on those gates;
  these defaults introduce no new required JSON-RPC fields or callbacks.
- **Approvals, MCP and CLI-only fixes.** Existing command/file/permission and
  elicitation request schemas are unchanged. [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0)
  improves enterprise MCP auth, credential masking, sandbox path/symlink checks,
  Windows proxy auth, config-override forwarding and permission-selection races.
  [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) fixes dropped
  remote MCP request cancellation and prevents queued agent messages waking work
  after interruption/Plan mode. These use existing protocols; extra review
  notifications remain observable as raw events. Fresh-TUI approval-reviewer
  defaults concern the TUI and do not replace PHP's explicit interactive
  `approvalPolicy`. Marketplace URL validation, installation/log cleanup and UI
  changes also ship in the CLI. Validate interrupt/cancel behavior in an isolated
  authenticated workflow if those features are used; no such run was performed.

## Actions and focused validation

| Classification | Action and focused validation |
| --- | --- |
| **Required new compatibility fixes** | None established for the consumed interfaces through 0.165.0; the prior corrections below remain in the assessed working tree. |
| **Resolved in working tree: event mappings** | Normalize known app-server command status and object patch kinds; preserve original notification/error payloads. Stable-schema fixtures for known/future statuses, add/delete/update/rename kinds, extra fields, and structured errors pass alongside exec parser tolerance regressions. |
| **Resolved in working tree: completion** | Interrupted completion now produces `TurnFailed` with upstream status and retained error payload. Completed/failed/interrupted fixtures and an approval-cancel workflow pass. `TurnResult::errors()` communicates non-success; process exit 0 alone remains insufficient. |
| **Resolved in working tree: interactive fork** | `thread/fork` uses the parent ID and returned child ID; request, configuration, invalid response, and subsequent resume tests pass. A focused authenticated isolated-thread smoke test would still validate inherited history beyond fake RPC assertions. |
| **Recommended consumer review** | Honor known backend ordinary-usage permission when admitting/retrying turns. Test positive percentages plus explicit `ordinaryUsageAllowed=false`, absent/null values, and sparse updates without erasing complete quota data. |
| **Recommended delivery/documentation** | Deliver the existing local fixes/features through the normal package release process and document a CLI minimum/range per transport after integration validation. This review identifies no new mandatory code change. Review checkpoints, local installation, and the handshake identifier are not support evidence. The implementation remains uncommitted; no publication was performed. |
| **Optional feature support** | Expose attached/prediction forks, sampling/memory/diagnostic APIs, file-ID image results and input metadata, voice-session correlation and client-metadata patches only for caller needs. Retain earlier Cyber, semantic-phase/lineage, native-dollar usage, richer-question, file-ID input, worktree/history/routing and realtime watch items. Add focused fixtures when implementing a typed feature; additive schemas and tolerated raw items alone do not require a package release. |
| **Usage completeness watch item** | For failed-turn cost accounting, consume typed root usage notifications or consider aggregate failure/interruption support. Validate reported counters followed by terminal failure/interruption; zero `TurnResult::usage` does not establish zero spending. |
| **Focused latest-stable integration checks** | Validate ordinary fork omission of `attachToSource`, compressed-history resume and saved settings versus explicit model/permission overrides; file-ID versus inline image result consumers; terminal `modelNotFound`; failed/interrupted usage and MCP cancellation. Retain immediate/late steering, nested-tool interruption and terminal incomplete-response checks. No authenticated backend checks were run. |
| **CLI-only upgrades** | New resume/config/compaction fixes, reasoning accounting, MCP auth/cancellation, credential masking, sandbox and marketplace URL hardening, model/provider catalogs, defaults and UI/runtime fixes ship in the CLI. The installed native CLI is already 0.165.0; no upgrade was performed. The PATH Node-launcher failure requires environment investigation if reproduced in deployment, not an inferred PHP compatibility change. |

## Unresolved uncertainties and limits

- No declared compatibility baseline, enforced range, version tag, or independently
  verified published PHP artifact was found. The verdict applies to the listed
  working tree, not an inferred released 1.7.0 artifact.
- Static schema/source checks and fake-server tests do not certify live turns,
  forked history, account routing, quotas, reset-credit consumption, approvals,
  steering latency, or realtime media. No authenticated call or credit redemption
  was made. The existing schema-based regressions lock down the identified fixes,
  but passing fake-server tests do not justify a blanket compatibility promise.
- Interactive usage reconstruction still needs sufficiently complete root token
  updates. Sparse notifications require caller-side merging/refetching; the
  package preserves them but cannot manufacture unreported quota sections. Failed
  or interrupted result aggregates may omit reported spending; the typed usage
  event stream remains the available accounting evidence (finding 10).
- Historical 0.157.1 release highlights remain incomplete. The prior verified
  [tag comparison](https://github.com/openai/codex/compare/rust-v0.157.0...rust-v0.157.1)
  and this run's tagged consumed-source checks are retained as evidence; brief
  notes alone were not used to infer support.
- Source access succeeded for the complete saved/new release inventory and
  tagged interfaces used here; upstream Git trees were untruncated and fetched
  source bytes matched Git blob hashes. The API window covers the saved/new
  range, not all older history. All 34 releases have note-review checkpoints;
  the current interface reassessment compares 0.163.0, 0.164.0 and 0.165.0 and does not
  runtime-certify intermediate versions. Baseline uncertainty prevents claims
  about unreviewed older versions. Reports before October 3 lacked note hashes;
  the retained hashes now support exact comparisons.
- The installed native CLI version/help succeeded, but the PATH launcher could not
  execute its Node interpreter in this environment. No launcher repair, live
  integration or deployment-specific executable validation was performed.

## What changed since the previous report

Reviewed **two new stable releases, 0.164.0 and 0.165.0**, published October 23
and October 27. The reviewed checkpoint advances from 0.163.0 to **0.165.0**.
All **32** previous note hashes and publication/update timestamps are unchanged;
the report retains **34** stable releases. New findings cover persisted settings,
ordinary versus attached forks, file-ID image results, terminal model-not-found
errors, reasoning accounting, provider/model catalogs, conditional defaults,
MCP behavior and runtime/security fixes. None establishes a new mandatory PHP
compatibility fix for consumed interfaces.

The 102 package assessment inputs remain byte-identical to the October 19
report. Existing fixes and unresolved findings were reassessed; all **144 tests /
717 assertions** pass, along with focused read-only raw-payload probes. The local
native CLI now reports **0.165.0**, replacing the local 0.163.0 observation, while
its PATH launcher could not execute Node. No upgrade or environment repair was
performed. The three inspected client quota files are unchanged at a newer HEAD;
sparse merging remains present and ordinary-usage admission remains unresolved.

The verdict remains **No update needed for the assessed working tree**. Existing
publication/support-range limits, failed-turn accounting, optional features,
consumer recommendations and authenticated-runtime uncertainties remain. No
supported baseline or published-package certification is inferred.

## Checkpoint for subsequent runs

Use this file as the persistent review state. Fetch releases fresh and paginate
to cover this inventory; filter stability flags and numeric versions before
comparison. Compare new stable releases, the exact note-body hashes below,
publication/stability metadata, and relevant package source/history/working-tree
changes. Reassess every unresolved finding even when no newer stable exists.
An input-fingerprint or repository revision change triggers inspection, not an
automatic report rewrite.

Update this report only for substantive new reviewed information, changed
compatibility, resolved findings, material note revisions, or a changed verdict.
If nothing substantive changes, leave the file **byte-for-byte unchanged**, with
this assessment date intact. If source access fails, preserve verified findings,
disclose the coverage limit, and do not advance the reviewed checkpoint.

The inventory hashes are SHA-256 of the exact decoded GitHub API `body` string
encoded as UTF-8, with existing line endings retained (`null` treated as empty).
They are comparison snapshots, not support certifications.

| Stable version | Published (UTC) | Release `updated_at` (UTC) | SHA-256 of exact API `body` UTF-8 |
| --- | --- | --- | --- |
| [0.147.0](https://github.com/openai/codex/releases/tag/rust-v0.147.0) | 2026-08-07T01:41:49Z | 2026-08-07T01:43:35Z | `694a00e39c80cfb27d387863469fb3b3277195685cd374d5de17475d3de91ac9` |
| [0.148.0](https://github.com/openai/codex/releases/tag/rust-v0.148.0) | 2026-08-18T22:26:03Z | 2026-08-18T22:27:29Z | `02a053b241b90dba7012a6a29a876d4dc48fad6cf6a5cc49a0af3f12e18bb7df` |
| [0.149.0](https://github.com/openai/codex/releases/tag/rust-v0.149.0) | 2026-08-20T21:04:55Z | 2026-08-20T21:06:13Z | `88d409ec767ff7c40095c9e642859ef53326b5c00f90f16d8b9c270c3412f319` |
| [0.149.1](https://github.com/openai/codex/releases/tag/rust-v0.149.1) | 2026-08-24T00:28:28Z | 2026-08-24T00:29:45Z | `dc12f0f81b5a5828eb473aecf359a97db0a8a2c386eb3d974b03084a74ab088e` |
| [0.150.0](https://github.com/openai/codex/releases/tag/rust-v0.150.0) | 2026-08-26T19:37:28Z | 2026-08-26T19:39:18Z | `6532d1b638923d8824635fad6c237c3f731cce4d8993e868663208625dc43d02` |
| [0.150.1](https://github.com/openai/codex/releases/tag/rust-v0.150.1) | 2026-08-27T01:56:54Z | 2026-08-27T01:58:35Z | `cb06eca98c043c93eb0621e4d0adf6604abacc5d6daddaf80642549ecfad2c5c` |
| [0.151.0](https://github.com/openai/codex/releases/tag/rust-v0.151.0) | 2026-08-29T09:55:39Z | 2026-08-29T09:57:02Z | `2cbf2f29290376dffc4d5c0f087c1ab610c8abff485a9d7c45bd8587ea0eeb76` |
| [0.152.0](https://github.com/openai/codex/releases/tag/rust-v0.152.0) | 2026-09-01T01:58:32Z | 2026-09-01T02:13:24Z | `d1a2f2b53c36b22682499571252c19a5212e740832c076bf76cba3466450c940` |
| [0.152.1](https://github.com/openai/codex/releases/tag/rust-v0.152.1) | 2026-09-01T22:33:02Z | 2026-09-01T22:34:23Z | `d905e4684973cbcb9c9c58d6eefaa44f42bbc6d2eef058a36f91281869a98054` |
| [0.153.0](https://github.com/openai/codex/releases/tag/rust-v0.153.0) | 2026-09-03T01:37:38Z | 2026-09-03T01:39:03Z | `db75be3522b08d45fb587f57ddfa56a1018602a6cda72f755154a754575baf35` |
| [0.153.1](https://github.com/openai/codex/releases/tag/rust-v0.153.1) | 2026-09-03T21:02:56Z | 2026-09-03T21:05:14Z | `464cb9c14ba9df13c72cfb7a2eb713e52c5a0d7d7ea9ac14357898025efb38d7` |
| [0.153.2](https://github.com/openai/codex/releases/tag/rust-v0.153.2) | 2026-09-03T23:53:12Z | 2026-09-03T23:54:50Z | `2578123f038057d26f940380b30dc4f3366fd2768e850556607f9dfd8d4fb568` |
| [0.153.3](https://github.com/openai/codex/releases/tag/rust-v0.153.3) | 2026-09-04T19:01:32Z | 2026-09-04T19:02:49Z | `43667145b787f5418e841fcf89223a27a4f42886c1ede237ccd854b833ca670c` |
| [0.153.4](https://github.com/openai/codex/releases/tag/rust-v0.153.4) | 2026-09-04T23:25:48Z | 2026-09-04T23:27:38Z | `a1194b9564911e6739cd8c5045220af094db275bc931e6ba3bc4b9d55caeba52` |
| [0.154.0](https://github.com/openai/codex/releases/tag/rust-v0.154.0) | 2026-09-09T22:35:38Z | 2026-09-09T22:37:29Z | `4f5d66c5c20427f8d40fa709ff8e1418fe945f162e698bffe40459156753fe1c` |
| [0.155.0](https://github.com/openai/codex/releases/tag/rust-v0.155.0) | 2026-09-17T23:14:43Z | 2026-09-17T23:16:01Z | `69603f1b9056fb8443dbb42a5860dceb142d6d7e5c003e864a482ae112016fd8` |
| [0.155.1](https://github.com/openai/codex/releases/tag/rust-v0.155.1) | 2026-09-18T20:03:04Z | 2026-09-18T20:05:11Z | `f7decb7da95a761c488597a109c7eba22bd02f5d8462b52ae6f48f1bd13ccfd5` |
| [0.156.0](https://github.com/openai/codex/releases/tag/rust-v0.156.0) | 2026-09-22T19:51:01Z | 2026-09-22T19:53:19Z | `1669e2f2ac2dc3170c7bd128c1109be3d7b1164b64d93fb91e1926e2c36d8ffc` |
| [0.156.1](https://github.com/openai/codex/releases/tag/rust-v0.156.1) | 2026-09-23T02:41:36Z | 2026-09-23T02:43:57Z | `65c671f209087f35ce0e85c8f12ca960ed3105bfd7bd7b20af7b96afa09080b4` |
| [0.157.0](https://github.com/openai/codex/releases/tag/rust-v0.157.0) | 2026-09-25T02:31:06Z | 2026-09-25T02:32:43Z | `98b092e0dfc624d32a1cd0d5ac35fd94dd76b5727d6ff5fe839c9cf0d5a186d1` |
| [0.157.1](https://github.com/openai/codex/releases/tag/rust-v0.157.1) | 2026-09-26T01:02:31Z | 2026-09-26T01:04:09Z | `50b12f264f22204a90a0b0942c4db54ef0c0a68939a35c41d74f22e5008de90f` |
| [0.158.0](https://github.com/openai/codex/releases/tag/rust-v0.158.0) | 2026-09-28T05:07:23Z | 2026-09-28T05:10:36Z | `378e77ea040b3fd9d75b692dc2239891365ba799c764ef7d1075395495400dfe` |
| [0.159.0](https://github.com/openai/codex/releases/tag/rust-v0.159.0) | 2026-09-29T08:05:42Z | 2026-09-29T08:07:30Z | `f0e1a4faad4a739444fb3d1acd439e4307e9397919e3d0c9723ef12619407284` |
| [0.159.1](https://github.com/openai/codex/releases/tag/rust-v0.159.1) | 2026-09-29T20:32:34Z | 2026-09-29T20:34:26Z | `4d6f87a95d3d93a4576bc1be2498c0cb51dcea3afb0382abd15bac978c8373e5` |
| [0.159.2](https://github.com/openai/codex/releases/tag/rust-v0.159.2) | 2026-09-29T23:57:16Z | 2026-09-30T00:00:27Z | `992a1b912164e14f51807c6a01b4ef1e12950860df6143205c0901047c686a69` |
| [0.159.3](https://github.com/openai/codex/releases/tag/rust-v0.159.3) | 2026-09-30T22:57:34Z | 2026-09-30T22:59:14Z | `a4a6cb2e232bdcb92eaf4313e1ffa83e50228d66e0458f5233ac7d9bbbdfd828` |
| [0.160.0](https://github.com/openai/codex/releases/tag/rust-v0.160.0) | 2026-10-01T20:19:13Z | 2026-10-01T20:21:40Z | `cc1ac8ec35b1ba705cb24ad5223e4bec92f0906dd0f462c46446a64f3d5d7b94` |
| [0.160.1](https://github.com/openai/codex/releases/tag/rust-v0.160.1) | 2026-10-05T18:29:37Z | 2026-10-05T18:31:33Z | `816ce06c6b21a3fe21822f580eb9b68a62f6bfdee3cfd4c44f057d76ce3e6ab5` |
| [0.161.0](https://github.com/openai/codex/releases/tag/rust-v0.161.0) | 2026-10-07T15:58:45Z | 2026-10-07T16:01:20Z | `24a99d4e9e15b7f29ce1245018e3e0cf3b447a890fc2971061ee446a35a67278` |
| [0.162.0](https://github.com/openai/codex/releases/tag/rust-v0.162.0) | 2026-10-08T18:55:59Z | 2026-10-08T18:58:03Z | `2b9c6cef6dc10c0352c3fdbfde5cb49105a6d75326553ad21d7eaae0747a850e` |
| [0.162.1](https://github.com/openai/codex/releases/tag/rust-v0.162.1) | 2026-10-09T19:44:51Z | 2026-10-09T19:47:24Z | `50f8671b5adbd8e0035a385c1b61fb245d5ac8da85bb9541eec4dba119f19ad9` |
| [0.163.0](https://github.com/openai/codex/releases/tag/rust-v0.163.0) | 2026-10-15T21:30:38Z | 2026-10-15T21:33:06Z | `0d6ae6535419b1984583879b4e07387ab4a91498efa52e3dd697a859de82055e` |
| [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0) | 2026-10-23T04:48:10Z | 2026-10-23T04:50:58Z | `49fc34aefdff50ddbab91f710d39eafbd1ac1167ba8ac186fdd34211addadd1c` |
| [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) | 2026-10-27T20:09:38Z | 2026-10-27T20:11:49Z | `ca2487ca0ed88cd77d499a2d7ea9bbf82880707bde94a79ca7d7958aa615fd0f` |
