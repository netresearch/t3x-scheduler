<!-- Managed by agent: keep sections and order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — workflows

<!-- AGENTS-GENERATED:START overview -->
## Overview
Every workflow here is a thin caller of a centralized reusable workflow in `netresearch/typo3-ci-workflows` or `netresearch/.github`. Repo-local logic is the exception, not the rule — change behavior upstream in the reusable, not by inlining steps here.
<!-- AGENTS-GENERATED:END overview -->

<!-- AGENTS-GENERATED:START filemap -->
## Key Files
| File | Purpose |
|------|---------|
| `ci.yml` | Extension test matrix (PHP 8.2-8.5 × TYPO3 ^13.4/^14.3, SQLite functionals, fractor) — the ONE per-extension file, intentional drift |
| `checks.yml` | Security/quality jobs + `All security checks` gate — byte-identical and drift-enforced across all typo3-extensions |
| `harness-verify.yml` | Agent-harness consistency check via `Build/Scripts/verify-harness.sh` |
| `release.yml` / `republish.yml` | TER/Packagist release and re-publish of a tag |
| `dco.yml`, `labeler.yml`, `community.yml`, `auto-merge-deps.yml` | DCO check, PR labeling, stale/lock/greetings, dependency auto-merge |
<!-- AGENTS-GENERATED:END filemap -->

## Setup
- Reusables are called with `@main` deliberately (own-org, tracked centrally); third-party actions used directly (e.g. harden-runner in the gate job) are pinned to full SHA.
- Default `permissions: {}` at workflow level; each `uses:` job grants exactly the reusable's caller contract.
- Secrets are passed explicitly (`secrets:` block in `ci.yml`); never `secrets: inherit`.

## Commands
| Task | Command |
|------|---------|
| Lint workflow security | `zizmor .github/workflows/` (same tool CI runs as required check) |
| Lint workflow syntax | `actionlint .github/workflows/*.yml` |
| Harness check (what `harness-verify.yml` runs) | `bash Build/Scripts/verify-harness.sh` (exit 2 = warnings only, passes) |

There is no local runner for the reusable-workflow jobs themselves — push to a PR branch and read the checks.

<!-- AGENTS-GENERATED:START code-style -->
## Workflow conventions
- **checks.yml is drift-enforced**: keep it byte-identical to the other typo3-extension repos; extension-specific settings belong in `ci.yml` only.
- **Any job added to checks.yml MUST also be added to `gate.needs`** — the `All security checks` gate is the only context rulesets require; a job missing there fails silently.
- Do not require pull-request-only jobs (`dependency-review`, `pr-quality`) or app-posted checks (CodeQL, zizmor, betterleaks, Opengrep) in rulesets — they never materialize on `merge_group` refs and stall the queue. Require the gate instead.
- `permissions:` blocks are minimal and per job; never `write-all`.
<!-- AGENTS-GENERATED:END code-style -->

<!-- AGENTS-GENERATED:START security -->
## Security & safety
- Never expose secrets in logs; pass them only via explicit `secrets:` mappings.
- Third-party actions: pin to full commit SHA with a version comment.
- The gate job treats `skipped` as pass and `failure`/`cancelled` as fail — do not "fix" that logic; it is what keeps the merge queue alive.
- zizmor findings on these files block merge (`zizmor` is a required check); suppressions use inline `# zizmor: ignore[...]` comments with a reason.
<!-- AGENTS-GENERATED:END security -->

<!-- AGENTS-GENERATED:START checklist -->
## PR/commit checklist
- [ ] New checks.yml job also added to `gate.needs`
- [ ] Reusable inputs match its current contract (check the reusable in netresearch/typo3-ci-workflows or netresearch/.github)
- [ ] Permissions block minimal, no `secrets: inherit`
- [ ] zizmor/actionlint pass (zizmor runs in CI and is required)
- [ ] No extension-specific config added to the drift-enforced `checks.yml`
<!-- AGENTS-GENERATED:END checklist -->

<!-- AGENTS-GENERATED:START examples -->
## Patterns to Follow
> **Prefer looking at real code in this repo over generic examples.**
> `ci.yml` shows the thin-caller-with-matrix pattern; `checks.yml` documents the gate rationale inline.
<!-- AGENTS-GENERATED:END examples -->

<!-- AGENTS-GENERATED:START help -->
## When stuck
- The reusable workflows live in https://github.com/netresearch/typo3-ci-workflows and https://github.com/netresearch/.github — read the called workflow before changing a caller.
- GitHub Actions reference: https://docs.github.com/en/actions
- The long comments inside `checks.yml` explain the gate and merge-queue traps — read them before touching required checks.
<!-- AGENTS-GENERATED:END help -->
