# Changelog

## 1.7.0 - 2026-09-19

- Accept a per-process environment on `Codex` and on the exec, app-server and app-server-turn transports, matching the `array<string, string|false>` convention of the other Develate CLI SDKs: a `false` entry removes a variable the parent would otherwise pass on. The default is unchanged — with no environment given, a Codex process inherits exactly what it inherited before.

## 2026-09-14

- Expose Codex app-server collaboration, model-reroute, lifecycle, and per-thread token-usage protocol events while retaining unrecognised notifications as raw events.
