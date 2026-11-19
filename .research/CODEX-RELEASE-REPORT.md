# Codex CLI Release Compatibility Report — `develate/codex-cli-php`

## Verdict: No update needed

No new required PHP compatibility fix is identified in the **current repository
implementation** for consumed stable interfaces through **0.170.0**. Four new
stable releases, **0.168.0, 0.169.0, 0.169.1 and 0.170.0**, have been reviewed
since the saved 0.167.2 checkpoint. Existing exec arguments, JSONL and consumed
app-server requests remain valid. The main behavior change is explicit project
trust for exec configuration/MCP loading; new message display, content-token,
account and history fields are retained raw or belong to optional APIs.
A newer CLI alone does not require a PHP package change.

The prior item, raw-payload, interruption, fork, steering, usage, permission and
child-thread fixes remain present. All 102 assessment inputs are byte-identical
to the previous report, and all existing tests pass. Consumer quota admission,
failed-turn usage aggregation and richer presentation remain watch items.
The latest local PHP tag **v1.8.0** predates the current fixes; this verdict does
not certify that artifact or remote publication. **No supported CLI minimum or
range is established; reviewing a release does not establish support for it.**

## Assessment metadata

- Date of last substantive assessment: **2026-11-19** (Europe/Vienna).
- Package repository: `code-cli/codex-cli-php`, HEAD
  **`408bfb61b4b5fd74d8e061a963b62af9d8561c25`**, unchanged from the saved report.
  Before this run **only `.research/CODEX-RELEASE-REPORT.md` was modified**;
  its existing November 12 contents were used as the checkpoint and preserved
  where still relevant. Implementation, documentation, tests and dependencies
  were clean. Only this report was edited during this assessment.
- Relevant package changes since the previous assessment: **none**. SHA-256
  assessment-input fingerprint **`ef996e790361fce3ac230c4957b3fd9dce2500f52de86f9fc966aaadaf39d48f`**
  is unchanged. Computed over 102 files: all `src/**/*.php`, `tests/**/*.php`,
  `README.md`, `CHANGELOG.md`, `composer.json`, `composer.lock` and
  `phpunit.xml.dist`; sorted relative POSIX paths, hashing
  `path UTF-8 + NUL + file bytes + NUL` per file. The report is excluded.
- Current source, README, changelog, version probes, inline/fake-executable
  fixtures and local tag/history evidence were inspected. Relevant history
  remains initial SDK `1ab829f`, reset credits `93c8483`, interactive approvals
  `f492150`, elicitation `6fddfcc`, initial research `e7a0e3d`, realtime
  `2925d5c`, permissions `28a0a29`, lifecycle/usage `4f307c7`, environment/
  collaboration `8bea1a3` and current fixes in `408bfb6`.
  No applicable `AGENTS.md` was found in the package, its ancestors or nested
  source/test/research directories. `code-client/AGENTS.md` was read for the
  retained read-only consumer reassessment.
- Fresh validation: `php vendor/bin/phpunit --do-not-cache-result --no-coverage`
  passed: **144 tests, 717 assertions**, PHP 8.5.3 / PHPUnit 11.5.56.
  Read-only PHP payload probes confirm original raw preservation for moderated
  agent messages with version-1 show/hide/block display instructions,
  `contentTokenCount`, `inferenceHookDenied` failures, unknown notifications
  and explicit quota `ordinaryUsageAllowed=false`. No test files were changed.
- Installed npm manifest and existing ARM64 native executable report CLI
  **0.170.0**. Native `exec`, `exec resume`, `exec fork` and `app-server` help
  retain consumed arguments, including `--stdio`. **The PATH launcher fails**
  with `Missing optional dependency @openai/codex-darwin-x64`; the native ARM64
  binary works and emits a nonfatal PATH-alias sandbox warning. This supersedes
  the prior working-launcher observation. No installation, upgrade or repair
  was performed. Default-binary calls can fail until the environment is repaired
  or an existing working executable is explicitly selected.
- Retained consumer finding reassessed in `code-client` at unchanged HEAD
  **`c85c99cb7cb92d8314ebe94d4daf8ed59a4c1965`**, in
  `app/Agent/QuotaService.php`, `app/Agent/Providers/CodexAdapter.php` and
  `app/Agent/CodexEventNormalizer.php`. Relevant files are unchanged; sparse
  merging remains present and ordinary-usage admission still uses percentages.
  The path-package alias remains **1.7.0**. Client tests were not rerun.
- Tests use inline fixtures and temporary fake executables. No authenticated
  turn/account call, project-trust mutation, credit redemption, live media,
  commit, push or publication was performed. Passing tests and help probes do
  not establish integration support for any CLI range.

## Version distinctions and compatibility baseline

| Meaning | Evidence and conclusion |
| --- | --- |
| PHP package version | Latest local tag **v1.8.0**, pointing to `8bea1a39de5be49dbae28cc1077d7ad2b0056323` (September 19). Current HEAD is later and has no version tag. `CHANGELOG.md`'s latest numbered entry and the client path alias still say **1.7.0**; the package's own `composer.json` has no version field. Tags v1.0.0–v1.8.0 exist locally. No remote publication or installed release artifact was verified. |
| Locally installed CLI | **0.170.0**, freshly reported by the existing native executable `/Users/thorsten/.nvm/versions/node/v22.22.2/lib/node_modules/@openai/codex/node_modules/@openai/codex-darwin-arm64/vendor/aarch64-apple-darwin/bin/codex --version`. The installed npm manifest also says 0.170.0. The PATH launcher currently fails because its requested darwin-x64 optional dependency is absent; the ARM64 native executable works. No upgrade or repair was performed. |
| Latest reviewed stable upstream | **0.170.0**, [`rust-v0.170.0`](https://github.com/openai/codex/releases/tag/rust-v0.170.0), published **2026-11-18T03:40:48Z**. |
| Supported compatibility baseline | **Unclear; no minimum or supported range is declared.** Current and initial README requirements ask for a working authenticated CLI without a version. `UnsupportedCodexVersion` remains unused; `Codex::version()` parses/caches a version but does not enforce support. `supportsInteractiveTurns()` checks transport configuration, not CLI capabilities. PHP tags do not document a Codex range. |

`ThreadTest`'s `0.150.1` and interactive fixtures' `0.157.1`/`0.160.0` are
fake-transport values, not integration certifications. Earlier release reviews
are checkpoints, not compatibility baselines. Because a baseline cannot be
established from repository evidence, this run assesses current code against the
**latest stable interfaces**, retains the historical reviewed range and makes no
blanket support promise for it. The `clientInfo.version` value **0.1.0** in
`AppServerClient::initialize()` is a separate handshake identifier.

The latest reviewed annotated tag resolves to
[commit `29eb79e9866b3262a777fdf8195c1b9e63684c91`](https://github.com/openai/codex/commit/29eb79e9866b3262a777fdf8195c1b9e63684c91).
Baseline evidence is in [README requirements](../README.md),
[`Codex::version()` / `supportsInteractiveTurns()`](../src/Codex.php),
[the unused exception](../src/Exception/UnsupportedCodexVersion.php), tests,
initial commit `1ab829f` and local tag history. Experimental realtime remains
conditional on installed CLI/backend capabilities.

## Fresh upstream coverage and reviewed release range

Fetched OpenAI's public [release page](https://github.com/openai/codex/releases)
and GitHub Releases API afresh:
[page 1](https://api.github.com/repos/openai/codex/releases?per_page=100&page=1),
[page 2](https://api.github.com/repos/openai/codex/releases?per_page=100&page=2),
[page 3](https://api.github.com/repos/openai/codex/releases?per_page=100&page=3) and
[page 4](https://api.github.com/repos/openai/codex/releases?per_page=100&page=4).
The **400 distinct records** reach stable **0.144.2**, older than the saved
0.147.0 inventory boundary. Filtered out drafts and GitHub prereleases,
normalized `rust-v` and `v` prefixes, accepted only anchored plain numeric
`major.minor.patch` CLI tags, and sorted integer tuples. Alpha, beta, rc,
preview, nightly, dev and other suffixed/non-CLI versions were excluded even
when public. Higher 0.171/0.172 alpha tags are not stable checkpoints.

- Previous reviewed inventory: **0.147.0–0.167.2, 38 stable releases**.
- Newly reviewed: **0.168.0, 0.169.0, 0.169.1 and 0.170.0**.
- All 38 saved releases remain stable/public. Their exact note-body SHA-256,
  publication dates and `updated_at` values are **unchanged**.
- Retained reviewed range: **0.147.0–0.170.0, 42 stable releases**. Exact note
  hashes and timestamps for all 42 are saved below. The fetched window contains
  50 plain stable versions; older records do not extend the reviewed range.
- Compared untruncated Git trees at **0.167.2, 0.168.0, 0.169.0, 0.169.1 and
  0.170.0** (10,866 / 11,108 / 11,312 / 11,315 / 11,347 entries). Relevant
  exec/JSONL and app-server protocol blob identities were compared across tags;
  changed consumed account/thread/turn/item/error definitions, exec trust,
  JSONL emitter and relevant feature/catalog source were read. Fourteen
  downloaded endpoint source blobs were additionally verified against Git blob
  hashes. This does not mean every upstream changed file was read.
- All four new full release notes were read. Sparse 0.169.1 highlights were
  supplemented with its complete 42-file tree difference and matching compare
  API list. That patch branch diverges from 0.169.0, so tag identities were
  checked separately rather than assuming linear ancestry. Fresh official
  [non-interactive](https://learn.chatgpt.com/docs/non-interactive-mode) and
  [app-server](https://learn.chatgpt.com/docs/app-server) docs corroborate current
  workflows; tagged code controls version-specific conclusions.
  All retained unresolved findings were reassessed against unchanged package
  code and the latest interfaces. No intermediate CLI is runtime-certified.

| Newly reviewed stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.168.0](https://github.com/openai/codex/releases/tag/rust-v0.168.0) | 2026-11-13T03:54:29Z | Steering/provider restoration fixes, completion content-token telemetry, optional audio and animated-image features, account-scoped history discovery. Existing consumed inputs/events remain valid. |
| [0.169.0](https://github.com/openai/codex/releases/tag/rust-v0.169.0) | 2026-11-13T08:37:05Z | Explicit project trust for exec config/MCP, raw moderation/display and account metadata, optional gateway/settings/history APIs, terminal-error and interruption fixes. |
| [0.169.1](https://github.com/openai/codex/releases/tag/rust-v0.169.1) | 2026-11-17T22:21:10Z | Sparse notes supplemented with tagged source: Bedrock incremental-tool defaults/commentary normalization and model catalogs. All exec/app-server protocol files match 0.169.0. |
| [0.170.0](https://github.com/openai/codex/releases/tag/rust-v0.170.0) | 2026-11-18T03:40:48Z | Sandbox/child restrictions, WebSocket fallback, OAuth and Cloud reconnect fixes; optional environment/MCP and TUI realtime features. No new mandatory PHP request fields. |

Stable releases retained from the November 12 review:

| Previously reviewed stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.166.0](https://github.com/openai/codex/releases/tag/rust-v0.166.0) | 2026-10-29T19:47:53Z | Preserves acknowledged permissions and reasoning accounting on resume/fork; fixes thread startup/shutdown. Adds flat item timestamps, optional native-dollar usage and metadata. No consumed command/payload removal. |
| [0.167.0](https://github.com/openai/codex/releases/tag/rust-v0.167.0) | 2026-11-06T01:40:00Z | Managed fresh-human checkpoints require an explicit client capability; unsupported clients reject those commands. Network prompt lifecycle, failed/missing-usage compaction and realtime history fixes ship in the CLI. Optional notification/input/error additions remain tolerated. |
| [0.167.1](https://github.com/openai/codex/releases/tag/rust-v0.167.1) | 2026-11-10T21:32:01Z | Notes contain only a changelog link. Tagged changes add default-disabled legacy Bedrock incremental tools and related core request/world-state handling. Consumed CLI/protocol schemas are unchanged from 0.167.0. |
| [0.167.2](https://github.com/openai/codex/releases/tag/rust-v0.167.2) | 2026-11-11T22:44:46Z | Default-enables legacy GCP incremental tool updates; Bedrock's separate flag stays default-disabled. Internal provider request/compaction behavior changes, with consumed exec/app-server contracts unchanged. |

Stable releases retained from the October 27 review:

| Stable release | Published (UTC) | Relevant assessment |
| --- | --- | --- |
| [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0) | 2026-10-23T04:48:10Z | Saved session settings/compressed-history restoration, config/compaction/MCP/sandbox fixes, file-ID image results and optional APIs. |
| [0.165.0](https://github.com/openai/codex/releases/tag/rust-v0.165.0) | 2026-10-27T20:09:38Z | Vertex AI account/provider, attached forks, conditional defaults, terminal model-not-found errors and MCP cancellation fixes. Previous checkpoint; retained findings remain below. |

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

### 1. Exec commands, JSONL, images, structured output and usage: no new required fix

[`ExecTransport::command()`](../src/Transport/ExecTransport.php) still constructs
valid start/resume/fork commands with `--json`, model, local images and output
schema. Start-only cwd, sandbox, color and writable-root flags are omitted on
resume/fork, which reuse persisted state. The latest
[exec CLI arguments](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/exec/src/cli.rs)
add optional `--trust`; existing argument forms are retained. The
[JSONL declarations](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/exec/src/exec_events.rs)
have the same Git blob at every compared tag from 0.167.2 through 0.170.0.
The [JSONL emitter](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/exec/src/event_processor_with_jsonl_output.rs)
only changes its final-message output-file binding/error propagation in this
comparison; PHP does not request that file. Its event mappings, final response
selection and token-counter fields remain. `Usage::fromArray()` and
`TurnResultBuilder` continue to consume them. Malformed JSONL and process
failures retain typed exception paths. Native 0.170.0 help accepts consumed
arguments; no authenticated exec run was made.

Unknown exec events/items become `UnknownEvent`; additional fields survive in
`raw()`. Parser fixtures cover this tolerance. Optional web-search/image output
and metadata do not themselves break parsing. Local image and schema inputs
remain valid; typed generated-image/file-ID handling remains optional. The
[official non-interactive reference](https://learn.chatgpt.com/docs/non-interactive-mode)
corroborates JSONL, schema and resume behavior. Calls omitting a model inherit
CLI/provider defaults; explicit model strings remain accepted. Optional
`--worktree`, Cyber controls and deliberate trust support are unexposed.
Finding 13 covers the new project-trust behavior and runtime/default changes.

### 2. App-server launch, RPCs, quotas and credits: consumed contracts retained

[`JsonRpcConnection::connect()`](../src/AppServer/JsonRpcConnection.php) launches
`codex app-server --stdio`, accepted by the latest
[CLI](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/cli/src/main.rs)
and native help as equivalent to `--listen stdio://`.
[`AppServerClient`](../src/AppServer/AppServerClient.php) initializes,
acknowledges `initialized` and sends account, quota/reset-credit, thread
start/resume/fork and turn start/steer/interrupt methods retained by the
[RPC registry](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/common.rs).
Consumed fields remain valid; registry/settings/history additions introduce no
mandatory calls for current workflows.

The [account/quota schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/account.rs)
adds optional account `userId` and gateway OAuth/security APIs. **Consumed quota,
credit, reset-credit parameters and all four reset-credit outcomes are unchanged**
from 0.167.2. This matters because `AppServerTransport` strictly enumerates
reset-credit outcomes, while `QuotaMapper` accepts additional quota fields and
preserves them in `Quota::raw`. Generic windows, additional buckets, credit and
nullable reset-credit details remain mapped. `Account` uses a provider-type
string and keeps the full original response, including workspace/user metadata.
The ordinary-usage consumer watch item remains unresolved (finding 7).
No account/backend or credit-consumption operation was exercised.

Unknown notifications remain `UnknownEvent` with original raw data. New provider,
gateway/device-sign-in, account security, environment/settings and history APIs
are optional. The [official app-server reference](https://learn.chatgpt.com/docs/app-server)
corroborates initialization, account, quota and reset-credit workflows. Managed
human-command capability requirements remain as described in finding 12.

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

The previous optional `turn/steer` feature is implemented in the **current
committed code** by `Thread::steer()`, `SteerableTransport`,
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
is byte-identical across 0.163.0–0.167.2 (including the intermediate stable tags). Interactive thread setup explicitly uses `on-request`, or `never`
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
  partial answers remain optional product decisions. The previously reviewed
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

### 11. Retained 0.164.0–0.165.0 changes and focused impact

- **Resume/fork persistence and overrides.**
  [0.164.0](https://github.com/openai/codex/releases/tag/rust-v0.164.0) fixes lookup
  of compressed histories and restores saved model/provider/reasoning settings.
  The [previously reviewed thread processor](https://github.com/openai/codex/blob/rust-v0.165.0/codex-rs/app-server/src/request_processors/thread_processor.rs)
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
  item. Both alternatives passed the November 12 read-only PHP preservation probes. This is an
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
  a closed PHP error enum. The November 12 failure probe passed. Consumers may classify
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

### 12. Retained 0.166.0–0.167.2 changes and focused impact

- **Flat item timestamps and image metadata.** The
  [0.166.0 item schema](https://github.com/openai/codex/blob/rust-v0.166.0/codex-rs/app-server-protocol/src/protocol/v2/item.rs)
  wraps Rust `ThreadItemPayload` with `#[serde(flatten)]` and optional
  `startedAtMs`/`completedAtMs`. The JSON item still has top-level `type`, `id`,
  text/status/output and other payload fields. `NotificationTranslator::item()`
  reads the same fields and the parser keeps the original notification. No PHP
  `payload` unwrapping is required. The
  [image-generation extension](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/ext/items/src/image_generation.rs)
  now serializes optional `generationId`; existing `fileId`/`result` alternatives
  and failure details remain raw. November 12 PHP probes passed for timestamps and
  both image alternatives. Typed duration/image/correlation support is optional.
- **Resume/fork settings and reasoning accounting.**
  [0.166.0](https://github.com/openai/codex/releases/tag/rust-v0.166.0) fixes
  acknowledged approval/permission restoration and persisted reasoning accounting.
  [Persisted settings](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server/src/request_processors/persisted_resume_settings.rs)
  prefer owner-known applied settings over stale turn context; the
  [thread processor](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server/src/request_processors/thread_processor.rs)
  preserves explicit sandbox/profile and approval overrides. PHP interactive
  `threadParams()` supplies explicit sandbox/approval settings; exec omits those
  start-only flags on resume/fork. Both existing request styles remain valid.
  [Reasoning-accounting changes](https://github.com/openai/codex/pull/57839/files)
  persist whether last usage already includes reasoning, avoiding recounting it
  on resume/fork. Public consumed token counters are not renamed. The
  [exec completion backfill](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/exec/src/lib.rs#L1749)
  also recovers terminal items via paginated history under backpressure, inside
  the CLI; PHP needs no new history RPC. Test saved settings versus explicit
  overrides, compaction after resumed history and final output under backpressure.
- **Managed commands requiring a person.**
  [0.167.0](https://github.com/openai/codex/releases/tag/rust-v0.167.0) introduces
  managed `requirements.toml` command rules with `decision = "prompt-user"`.
  The [tagged app-server documentation](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server/README.md)
  requires both `experimentalApi: true` and `requiredHumanApproval: true` before
  a client can receive `humanCommand` approvals. Without a capable connection,
  the command is rejected. The
  [capability schema](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server-protocol/src/protocol/v1.rs)
  defaults the new flag to false; the
  [exec client](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/exec/src/lib.rs)
  also sets it false. PHP `AppServerClient::initialize()` does not advertise it,
  so this managed workflow is **not supported by the current wrapper**. Ordinary
  approval and permission contracts remain usable. Supporting these checkpoints
  is optional unless deployment policy requires them; it needs a real human
  presentation path and one-time accept/decline/cancel decisions. Advertising the
  capability for an automated callback would not fulfill that contract. Validate
  unsupported rejection and a reviewed human decision if adding support.
- **Accounts, notifications and input additions.** Nullable `workspaceName` and
  experimental account-session/GCP setup APIs do not affect current account
  reads. New `thread/section/changed` notifications are raw `UnknownEvent`.
  The [turn schema](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server-protocol/src/protocol/v2/turn.rs)
  adds optional names to additional context and a file-ID audio alternative.
  Uploaded audio file input is explicitly
  [rejected at runtime in this build](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/core/src/session/turn.rs#L1048);
  schema acceptance does not establish usable audio input. PHP sends neither
  additional-context nor audio inputs. Local-image/text input, output schemas
  and steering keep their consumed forms. Richer typed APIs are optional and
  should be validated for an intended workflow.
- **Errors and usage metadata.** The
  [error schema](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/app-server-protocol/src/protocol/v2/shared.rs)
  adds `policyCapacityUnavailable`. `TurnFailed` and `ErrorEvent` retain errors
  and raw data; the PHP code has no closed error enum to update. A focused November 12
  failure probe passed. The
  [thread-usage schema](https://github.com/openai/codex/blob/rust-v0.166.0/codex-rs/app-server-protocol/src/protocol/v2/thread_usage.rs)
  adds optional `nativeUsageUsdMicros`, distinct from credit estimates and token
  counts; missing/null means unknown, not zero. This package does not call that
  usage-read RPC. Typed billing support is optional. The 0.167.0 compaction fix
  includes retained output after failed/missing-usage responses; it does not fix
  PHP's failed/interrupted result-aggregate limitation (finding 10).
- **Provider tool defaults and CLI runtime upgrades.** Sparse
  [0.167.1 notes](https://github.com/openai/codex/releases/tag/rust-v0.167.1) were
  supplemented with the [tag comparison](https://github.com/openai/codex/compare/rust-v0.167.0...rust-v0.167.1):
  11 changed files, including core tool world state, turn/compaction handling,
  retained top-level hosted web-search configuration and the default-disabled
  `incremental_tools_legacy` Bedrock flag. The next
  [tag comparison](https://github.com/openai/codex/compare/rust-v0.167.1...rust-v0.167.2)
  changes seven files. Its
  [feature defaults](https://github.com/openai/codex/blob/rust-v0.167.2/codex-rs/features/src/lib.rs)
  enable the separate `incremental_tools_legacy_gcp` flag; setting it false opts
  out, while Bedrock's separate flag stays false. These affect provider request
  construction, not the wrapper's event/argument contract. Consumed CLI/protocol
  blobs are identical at 0.167.0/0.167.1/0.167.2. Network prompt lifecycle,
  realtime-history retention, app-server startup/shutdown, MCP sharing and
  authentication, model catalogs, standalone packaging and TUI fixes ship in
  the CLI. No PHP install/upgrade was performed.

### 13. New 0.168.0–0.170.0 changes and focused impact

- **Project trust now stays explicit in exec.**
  [0.169.0](https://github.com/openai/codex/releases/tag/rust-v0.169.0) corrects
  implicit project trust under `--sandbox workspace-write`. Untrusted project
  configuration and MCP servers stay disabled; the command can run with those
  layers omitted and warning on stderr. The
  [latest exec implementation](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/exec/src/lib.rs#L551)
  only persists trust when the caller explicitly requests `--trust` and no saved
  decision exists; an explicitly untrusted project is refused. The embedded
  exec app-server uses a default cwd rather than granting trust through explicit
  thread startup cwd. PHP's existing arguments remain valid, but deployments
  relying on local config/MCP need a deliberately trusted project. Exposing an
  explicit trust choice is optional; automatically appending `--trust` would
  change authority. Interactive PHP still supplies explicit `cwd` under its
  existing thread-start contract; do not assume its trust path is identical to
  exec. Validate pretrusted/untrusted directories, warnings, MCP discovery and
  start/resume/fork using isolated configuration; no real trust was changed.
- **Message display metadata is preserved, not interpreted.** The
  [item schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/item.rs)
  adds optional agent-message `moderation` with `moderated` and `display`.
  The [version-1 display contract](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/protocol/src/items/display.rs)
  describes recovered Chat-answer presentation with `mode: show/hide/block`,
  annotations, replacement/reason and trusted-contact hints. Unreadable stored
  display keeps the upstream moderation flag restrictive. `NotificationTranslator`
  and `JsonlEventParser` preserve the whole original notification; typed
  `AgentMessage::text` and `TurnResultBuilder` do **not** apply those presentation
  instructions. This is an optional presentation-support gap for consumers
  implementing such replay UIs, not a rejected wire payload. Validate raw
  version-1 show/hide/block, absent display and unknown versions before adding
  presentation support; retaining text alone does not implement that contract.
- **Content telemetry is separate from turn usage.**
  [Item completion](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/item.rs)
  adds nullable `contentTokenCount` for native assistant-content tokens, including
  a sampled footer. It is completion telemetry, not stored item data or a
  replacement for `Usage` counters. PHP preserves it raw. Root token-notification
  and successful-turn reconstruction fields remain; failed/interrupted aggregate
  accounting still has the limitation in finding 10. Read-only probes confirm
  display/content fields and `inferenceHookDenied` errors survive translation.
- **History, account and environment APIs remain optional.** The
  [thread schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/thread.rs)
  adds account-scoped history filtering, attached-thread search, queued-input
  metadata, prepared-plan handoff and experimental deferred command/MCP history
  outputs. Deferral requires opt-in; this PHP package calls none of these APIs.
  Existing start/resume/fork and text/local-image turn inputs remain valid.
  Thread/turn settings may update environments; these are optional APIs, not
  required changes to PHP's child-process `env`. Optional account `userId`,
  gateway/device-sign-in and security notices stay raw or uncalled.
- **Sandbox, approvals and terminal errors remain usable.**
  [0.170.0](https://github.com/openai/codex/releases/tag/rust-v0.170.0) fixes
  permission updates and child threads losing prior restrictions; legacy
  sandbox/profile conversions and resume overrides are handled in the CLI.
  The consumed [permission schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/permissions.rs)
  and [initialization capabilities](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v1.rs)
  match 0.167.2. Ordinary command/file/MCP approval callbacks remain applicable;
  managed human-command restrictions remain unresolved optional support.
  The [error schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/shared.rs)
  adds `inferenceHookDenied`; CLI terminal classification also avoids retrying
  explicit authentication/workspace/access-program/content-policy denials.
  PHP retains message/status/structured raw errors without a closed error enum.
  Validate persisted restrictions across resume/fork, denied actions, terminal
  failure and cancellation followed by a new turn; upstream runtime fixes do
  not resolve PHP's failed-turn aggregate-usage gap.
- **Audio/image and realtime features are optional.**
  [0.168.0](https://github.com/openai/codex/releases/tag/rust-v0.168.0) enables
  uploaded recordings for audio-capable models with rejected-recording recovery
  and duration budgeting. This supersedes the runtime rejection noted for the
  older 0.167.2 build in finding 12; PHP still sends text/local-image inputs.
  GIF generation remains feature-gated and default-disabled in the
  [latest feature source](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/features/src/lib.rs).
  Generated image/animation payloads remain raw. The
  [realtime RPC schema](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/app-server-protocol/src/protocol/v2/realtime.rs)
  is unchanged from 0.167.2; TUI WebRTC negotiation does not require new PHP
  fields. Neither media backend availability nor a live session was tested.
- **Sparse patch notes and provider defaults were checked by tag.**
  [0.169.1 notes](https://github.com/openai/codex/releases/tag/rust-v0.169.1)
  have no verified highlights. Its
  [comparison](https://github.com/openai/codex/compare/rust-v0.169.0...rust-v0.169.1)
  is divergent (four added commits, one behind; 42 changed files), but every
  exec/app-server protocol file matches 0.169.0. Tagged changes default-enable
  Bedrock legacy incremental tools, normalize its completed commentary and add
  GPT-6.2 Sol/Astra catalogs. The
  [latest features](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/features/src/lib.rs)
  and [catalog](https://github.com/openai/codex/blob/rust-v0.170.0/codex-rs/models-manager/models.json)
  retain those defaults/catalog entries. Other new defaults include retained
  client-tool outputs, unified image budgeting, untrusted context as tool output
  and Code Mode gRPC/output truncation. PHP accepts model strings and inherits
  CLI defaults; no new argument or token-counter shape is required.
- **CLI-only runtime upgrades.** Steering during final transcript flush, saved
  provider restoration, interruption/late-abort isolation, oversized WebSocket
  HTTP fallback, MCP OAuth races, Cloud reconnection and Windows runtime fixes
  ship upstream. New same-name MCP/environment support is experimental.
  These changes may improve execution but do not by themselves require a PHP
  release. Native 0.170.0 is already installed; its failing Node/PATH launcher
  is a separate local environment problem, not a demonstrated protocol break.

## Actions and focused validation

| Classification | Action and focused validation |
| --- | --- |
| **Required new compatibility fixes** | None established for consumed interfaces through 0.170.0. Retained corrections are present in current committed code. |
| **Resolved: mappings and completion** | Known command status, object patch kinds, original raw notifications/errors and interrupted status remain covered by passing fixtures. `TurnResult::errors()` communicates non-success; process exit 0 alone remains insufficient. |
| **Resolved: fork, steering and usage** | Actual `thread/fork` request/child routing, invalid child IDs, subsequent resume, immediate/late steering, child-thread filtering and successful-turn usage reconstruction remain covered by fake-server tests. Validate history/settings inheritance and nested-tool interruption in an isolated live workflow when needed. |
| **Recommended consumer review** | Honor known backend ordinary-usage permission before admission/retry. Validate positive percentages plus explicit `ordinaryUsageAllowed=false`, absent/null and sparse updates without erasing full quota data. |
| **Delivery and support-range documentation** | Current fixes are committed after local v1.8.0; deliver through the normal package release process if distributing the tagged artifact. Align numbered changelog/client alias and document a CLI minimum/range per transport after integration validation. No new implementation fix is inferred from tag/version differences alone. |
| **Optional managed-human workflow** | Add `requiredHumanApproval` only with a real human UI for managed `prompt-user` commands; current exec/PHP clients reject these protected commands without a capable connection. Validate rejection and one-time decisions. Existing automated approval callbacks do not establish this capability. |
| **Optional typed feature support** | Moderation/display, content-token telemetry, account user IDs and new settings/history APIs can remain raw or uncalled. Item timestamps, workspace/account-session details, native-dollar billing, image generation/file IDs and section notifications can remain raw. Retain attached/prediction forks, sampling/memory/diagnostic APIs, richer questions/plans, semantic phases/lineage, Cyber/worktree/history/routing and realtime watch items. Add targeted validation when implementing a feature. |
| **Usage completeness watch item** | Consume typed root token updates for failed-turn accounting or consider aggregate failure/interruption support. Validate counters followed by failure/interruption and total deltas against a known baseline; zero aggregate usage does not prove zero spending. |
| **Focused latest-stable integration checks** | Check explicit project trust and local config/MCP discovery in isolated profiles, moderated replay presentation if used, then persisted approval/permissions versus explicit resume/fork overrides, reasoning accounting across history restoration, flat timed items, terminal policy/model errors and failed usage. For affected deployments, validate GCP incremental tools and optional managed-human rules. No authenticated backend checks were run. |
| **CLI-only upgrades / environment** | Runtime/auth/sandbox/MCP, provider defaults and TUI changes ship in Codex. Native CLI and npm manifest report 0.170.0; the PATH launcher fails on a missing darwin-x64 optional dependency. Repair/select a working launcher through the normal environment process if needed. No software was installed, upgraded or repaired. |

## Unresolved uncertainties and limits

- **No declared compatibility baseline or supported range.** Initial/current
  requirements, tags and fake CLI versions do not establish a CLI minimum.
  Latest PHP v1.8.0 predates committed corrections; no published artifact was
  verified. The verdict concerns current repository code and latest consumed
  stable interfaces, not every CLI version in the historical review range.
- Static tag review, native help and fake-server tests do not certify live turns,
  history/fork restoration, account/provider routing, quotas, reset-credit
  redemption, approvals, human-command rules, steering latency or realtime.
  No authenticated API or trust mutation was performed. PATH launcher failure
  prevents normal default-binary use in this environment; native help succeeds.
- Ordinary-usage admission in the inspected consumer still ignores backend
  permission retained in `Quota::raw`. Sparse updates require caller-side
  merging/refetching. Successful usage reconstruction needs sufficiently complete
  root updates; failed/interrupted result aggregates may omit reported spending.
  All these retained limitations persist in unchanged code.
- Managed human commands are rejected without a capable human client. Typed
  plans/questions, message phases, moderation/display, content-token/billing/
  image metadata and optional audio/settings/history APIs remain incomplete
  feature support. Raw preservation does not implement a recovered-answer UI's
  display instructions. Schema presence does not prove backend availability.
- Exec's explicit project-trust requirement can change local config/MCP
  availability. The wrapper exposes no deliberate `--trust` choice; consumers
  needing it must establish trust through their normal setup. The review did
  not authorize or change actual trust. Interactive explicit-cwd and exec
  embedded-client paths should be checked separately for intended deployments.
- Historical 0.157.1 highlights remain incomplete; retained
  [tag comparison](https://github.com/openai/codex/compare/rust-v0.157.0...rust-v0.157.1)
  and consumed-source evidence remain. Sparse 0.167.1 and 0.169.1 notes were
  supplemented by tagged changes; neither sparse notes nor a changelog link
  alone establish support.
- The 400-record inventory covers every saved/new stable release, not every
  older release. Full tree comparisons, selected source reads and blob checks
  support the consumed-interface findings, not a line-by-line audit of every
  upstream change. Exploratory source-path 404s were recovered using verified
  tagged paths; no required source gap remained. This run's detailed comparison
  is **0.167.2–0.170.0**. Findings 3–12 retain earlier verified observations,
  reassessed against unchanged package code; their older tagged behavior is
  historical where explicitly superseded by finding 13.

## What changed since the previous report

Reviewed **four new stable releases, 0.168.0, 0.169.0, 0.169.1 and 0.170.0**,
advancing the review checkpoint from 0.167.2 to **0.170.0** (November 18).
All **38** previous note hashes and publication/update timestamps are unchanged;
**42** stable releases are now retained. New findings cover explicit exec project
trust, moderation/display and completion telemetry, account/environment/history
APIs, enabled uploaded audio, provider/default changes and runtime fixes.
No new mandatory PHP compatibility fix was established; the verdict remains
**No update needed for current code**.

Package HEAD, its 102-file fingerprint and the relevant consumer checkout are
unchanged. All **144 tests / 717 assertions** pass, with focused read-only raw
payload probes. Installed native CLI is now **0.170.0**; its help probes pass,
but the PATH launcher currently fails on a missing optional dependency,
superseding the previous working-launcher observation. No repair was made.
Existing quota-admission, sparse-state, failed-turn usage, human-command,
optional presentation and delivery/baseline findings are retained.

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
| [0.166.0](https://github.com/openai/codex/releases/tag/rust-v0.166.0) | 2026-10-29T19:47:53Z | 2026-10-29T19:50:20Z | `f67845b2d634b8679cdb4ac0ded4533471d76391bc8a90df2a01f59586168666` |
| [0.167.0](https://github.com/openai/codex/releases/tag/rust-v0.167.0) | 2026-11-06T01:40:00Z | 2026-11-06T01:42:19Z | `1c954a1b3521436a10c1bd5b8c2951db4dee4fad4c1a0e911dfd72ee8ed1bf4f` |
| [0.167.1](https://github.com/openai/codex/releases/tag/rust-v0.167.1) | 2026-11-10T21:32:01Z | 2026-11-10T21:33:57Z | `cc8c16e1701844ff9d66848dae004ee68155a77a6000f98e93ff4987f32b5d54` |
| [0.167.2](https://github.com/openai/codex/releases/tag/rust-v0.167.2) | 2026-11-11T22:44:46Z | 2026-11-11T22:46:17Z | `dfac3dca97f82dc5a09577eb6ed163ef6eabb877e866cd905701535a9302146f` |
| [0.168.0](https://github.com/openai/codex/releases/tag/rust-v0.168.0) | 2026-11-13T03:54:29Z | 2026-11-13T03:56:26Z | `2d98ba4e26fdf59040bb3d9accf2e7f4e85932e92ee03071f22a4c452df10b39` |
| [0.169.0](https://github.com/openai/codex/releases/tag/rust-v0.169.0) | 2026-11-13T08:37:05Z | 2026-11-13T08:39:10Z | `d6cbab2b561d631d647a60f3c6fd7ae3d3f4a5a4c53243fdb35a63b9296a94e3` |
| [0.169.1](https://github.com/openai/codex/releases/tag/rust-v0.169.1) | 2026-11-17T22:21:10Z | 2026-11-17T22:22:49Z | `0b23a867b21b990f3cede569e3f9b22ecdaa606dc817784c61d5f05686127eb7` |
| [0.170.0](https://github.com/openai/codex/releases/tag/rust-v0.170.0) | 2026-11-18T03:40:48Z | 2026-11-18T03:45:39Z | `b3de3d55463e2d7b52e559fedd576e416b977389752595d9072aaa64210249ed` |
