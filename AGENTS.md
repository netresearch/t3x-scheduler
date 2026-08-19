<!-- FOR AI AGENTS - Human readability is a side effect, not a goal -->
<!-- Managed by agent: keep sections and order; edit content, not structure -->
<!-- Last updated: 2026-08-19 | Last verified: 2026-08-19 -->

# AGENTS.md

**Precedence:** the **closest `AGENTS.md`** to the files you're changing wins. Root holds global defaults only. Component map: `docs/ARCHITECTURE.md`.

## Commands
> Source: composer.json scripts + Makefile (verified 2026-08-19). Binaries land in `.build/bin/`, not `vendor/bin/`.

<!-- AGENTS-GENERATED:START commands -->
| Task | Command |
|------|---------|
| Install | `composer install` |
| Lint (syntax) | `composer ci:test:php:lint` |
| Code style check | `composer ci:test:php:cgl` (or `make cgl`) |
| Code style fix | `composer ci:cgl` (or `make cgl-fix`) |
| PHPStan | `composer ci:test:php:phpstan` (or `make phpstan`) |
| Rector dry-run | `composer ci:test:php:rector` (or `make rector`) |
| Fractor dry-run | `composer ci:test:php:fractor` |
| Unit tests | `composer ci:test:php:unit` |
| Functional tests | `typo3DatabaseDriver=pdo_sqlite composer ci:test:php:functional` |
| Full suite | `composer ci:test` |
<!-- AGENTS-GENERATED:END commands -->

> If commands fail, verify against composer.json/Makefile or ask user to update.

## Response Style
- Answer first, elaborate only if needed. No sycophantic openers ("Great question!", "Absolutely!").
- For yes/no or status questions, lead with the answer.
- Skip preamble. Match response length to task complexity.

## Workflow
1. **Before coding**: Read nearest `AGENTS.md` + check Golden Samples for the area you're touching
2. **After each change**: Run the smallest relevant check (lint → phpstan → single test)
3. **Before committing**: Run full test suite if changes affect >2 files or touch shared code
4. **Before claiming done**: Run verification and **show output as evidence** — never say "try again", "should work now", "tested", "verified", or "all green" without pasted command output in the same turn

## File Map
<!-- AGENTS-GENERATED:START filemap -->
```
Classes/         → PHP source (PSR-4: Netresearch\NrScheduler\)
Configuration/   → Services.yaml (DI: autowire + autoconfigure)
Resources/       → XLF language files, extension icon
Tests/           → unit + functional tests (typo3/testing-framework)
Build/           → tool configs (phpstan, rector, fractor, phpunit XML) + Scripts/
.github/         → workflows: thin callers of netresearch reusable workflows
```
<!-- AGENTS-GENERATED:END filemap -->

## Golden Samples (follow these patterns)
<!-- AGENTS-GENERATED:START golden-samples -->
| For | Reference | Key patterns |
|-----|-----------|--------------|
| Functional test | `Tests/Functional/AbstractTaskTest.php` | FunctionalTestCase, extension loading |
| Unit test | `Tests/Unit/Fields/FieldRenderingTest.php` | UnitTestCase, data providers |
| Field type | `Classes/Fields/TextField.php` | extends `Classes/Fields/AbstractField.php` |
<!-- AGENTS-GENERATED:END golden-samples -->

## Heuristics (quick decisions)
<!-- AGENTS-GENERATED:START heuristics -->
| When | Do |
|------|-----|
| Adding a field type | Extend `Classes/Fields/AbstractField.php`; mirror an existing field |
| Adding a validator | Extend `Classes/Validators/AbstractValidator.php` |
| Adding a class | PSR-4 under `Classes/`; DI is autowired via `Configuration/Services.yaml` |
| Running tasks | `make help` lists Make targets; full list in composer.json `scripts` |
| Committing | Conventional Commits, DCO sign-off + signature: `git commit -S --signoff` |
| Adding dependency | Ask first - we minimize deps |
| Unsure about pattern | Check Golden Samples above |
<!-- AGENTS-GENERATED:END heuristics -->

## Repository Settings
<!-- AGENTS-GENERATED:START repo-settings -->
- **Default branch:** `main`
- **Merge strategy:** merge
- **Signed commits:** required
- **Required checks (rulesets):** `All security checks`, `CodeQL`, `DCO`, `Opengrep OSS`, `betterleaks`, `ci / All CI checks`, `dco / DCO`, `scorecard`, `zizmor`
- **Active rulesets:** Copilot review for default branch, Require DCO sign-off, require-signed-commits, t3x-baseline, t3x-pull-request
<!-- AGENTS-GENERATED:END repo-settings -->

<!-- AGENTS-GENERATED:START ci-rules -->
## CI
- `.github/workflows/ci.yml` calls reusable `netresearch/typo3-ci-workflows/.github/workflows/ci.yml@main` with matrix PHP 8.2-8.5 × TYPO3 `^13.4`/`^14.3`, functional tests on SQLite, fractor enabled.
- Security/quality jobs (CodeQL, gitleaks, zizmor, fuzz, license-check) live in `.github/workflows/checks.yml` — do not duplicate them into ci.yml. See `.github/workflows/AGENTS.md`.
<!-- AGENTS-GENERATED:END ci-rules -->

## Boundaries

### Always Do
- Add tests for new code paths
- Use conventional commit format: `type(scope): subject`
- Use **atomic commits** (one logical change per commit); preserve signatures, keep bisection useful
- **Show test output as evidence before claiming work is complete**
- Before any edit, verify `pwd` resolves inside the intended repo worktree — not `.bare/`, not `~/.claude/skills/…`, not `~/.claude/plugins/cache/…` (read-only caches)
- For upstream dependency fixes: run **full** test suite, not just affected tests
- Force-push only with `--force-with-lease`
- Follow PSR-12 and PHP ^8.2; `declare(strict_types=1);` in every PHP file

### Ask First
- Adding new dependencies
- Modifying CI/CD configuration
- Changing public API signatures (this extension is a library other extensions build on)
- Repo-wide refactoring or rewrites

### Never Do
- Commit secrets, credentials, or sensitive data
- Commit `composer.lock` or anything under `.build/` (both gitignored; extension ships without a lock file)
- Push directly to `main` — open a PR
- Merge a PR before all review threads are resolved
- Squash commits during merge or rebase unless the user explicitly asked
- Reply to review comments with bare "Addressed" or "Fixed" — cite the resolving commit SHA
- Build new code on `Classes/AbstractAdditionalFieldProvider.php` (deprecated, see Codebase State)
- Use `secrets: inherit` in reusable GitHub Actions workflows (pass secrets explicitly)

## Codebase State
<!-- AGENTS-GENERATED:START codebase-state -->
- `Classes/AbstractAdditionalFieldProvider.php` is `@deprecated` since 2.0.0: it wraps `\TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider`, which TYPO3 removes in v15. Migrate consuming tasks to native task types with TCA additional fields.
- Version source of truth: `ext_emconf.php`; PHPStan runs at level 6 with baseline `Build/phpstan-baseline.neon`.
<!-- AGENTS-GENERATED:END codebase-state -->

## Scoped AGENTS.md (MUST read when working in these directories)
<!-- AGENTS-GENERATED:START scope-index -->
- `./Classes/AGENTS.md` — PHP source: scheduler task base classes, fields, validators
- `./Tests/AGENTS.md` — unit + functional test suites
- `./.github/workflows/AGENTS.md` — CI/CD: thin callers of centralized reusable workflows
<!-- AGENTS-GENERATED:END scope-index -->

> **Agents**: When you read or edit files in a listed directory, you **must** load its AGENTS.md first. It contains directory-specific conventions that override this root file.

## When instructions conflict
The nearest `AGENTS.md` wins. Explicit user prompts override files.
