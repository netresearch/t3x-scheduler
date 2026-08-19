<!-- Managed by agent: keep sections and order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Classes

<!-- AGENTS-GENERATED:START overview -->
## Overview
PHP source of the `nr_scheduler` TYPO3 extension: base classes that other Netresearch extensions build scheduler tasks on. No controllers, no Extbase domain models, no ViewHelpers — this is a library of abstract classes, field types, validators, and traits.
<!-- AGENTS-GENERATED:END overview -->

<!-- AGENTS-GENERATED:START filemap -->
## Key Files
| File | Purpose |
|------|---------|
| `AbstractTask.php` | Base class for scheduler tasks, extends `\TYPO3\CMS\Scheduler\Task\AbstractTask` |
| `AbstractAdditionalFieldProvider.php` | **Deprecated since 2.0.0** — wraps a TYPO3 API removed in v15; do not build on it |
| `Fields/AbstractField.php` | Base for additional-field input types (Text, TextArea, Select, MultiSelect, CheckBox, Password) |
| `Validators/AbstractValidator.php` | Base for field validators |
| `Traits/FlashMessageTrait.php`, `Traits/TranslationTrait.php` | Backend flash messages, XLF label translation |
| `Exception.php` | Extension-specific exception type |
<!-- AGENTS-GENERATED:END filemap -->

<!-- AGENTS-GENERATED:START golden-samples -->
## Golden Samples (follow these patterns)
| Pattern | Reference |
|---------|-----------|
| Concrete field type | `Fields/TextField.php` |
| Task base usage | `../Tests/Fixtures/DummyTask.php` |
<!-- AGENTS-GENERATED:END golden-samples -->

<!-- AGENTS-GENERATED:START setup -->
## Setup & environment
- Install: `composer install` (binaries in `.build/bin/`)
- PHP: ^8.2 · TYPO3: ^13.4 || ^14.3 (core, fluid, scheduler)
- Composer package `netresearch/nr-scheduler`, extension key `nr_scheduler`
<!-- AGENTS-GENERATED:END setup -->

<!-- AGENTS-GENERATED:START structure -->
## Directory structure
```
Classes/
  Fields/       → additional-field input types (extend AbstractField)
  Traits/       → FlashMessageTrait, TranslationTrait
  Validators/   → field validators (extend AbstractValidator)
```
<!-- AGENTS-GENERATED:END structure -->

<!-- AGENTS-GENERATED:START commands -->
## Build & tests
| Task | Command |
|------|---------|
| Lint | `composer ci:test:php:lint` |
| Code style fix | `composer ci:cgl` |
| PHPStan | `composer ci:test:php:phpstan` |
| Unit tests | `composer ci:test:php:unit` |
| Functional tests | `typo3DatabaseDriver=pdo_sqlite composer ci:test:php:functional` |
| Full suite | `composer ci:test` |
<!-- AGENTS-GENERATED:END commands -->

<!-- AGENTS-GENERATED:START code-style -->
## Code style & conventions
- PSR-12 via php-cs-fixer (`Build/.php-cs-fixer.dist.php`); `declare(strict_types=1);` in every file
- Namespace `Netresearch\NrScheduler\` (PSR-4 from `Classes/`)
- DI: constructor injection, autowired via `Configuration/Services.yaml`; avoid `GeneralUtility::makeInstance()` in new code
- File header: package/license docblock as in every existing file
- PHPStan level 6 with strict rules + deprecation rules; baseline in `Build/phpstan-baseline.neon` — do not grow it, fix findings instead
<!-- AGENTS-GENERATED:END code-style -->

<!-- AGENTS-GENERATED:START security -->
## Security & safety
- Field values come from scheduler backend forms: validate via `Validators/` before use
- `Fields/PasswordField.php` exists so secrets are masked in the backend — never render credentials with a plain `TextField`
- Field HTML is built with Fluid's `TagBuilder`: `addAttribute()` escapes by default, but `setContent()` is raw — escape content explicitly when it can carry user input
- Backend-only code: no frontend rendering, no direct request handling here
<!-- AGENTS-GENERATED:END security -->

<!-- AGENTS-GENERATED:START checklist -->
## PR/commit checklist
- [ ] `composer ci:test` passes locally (functional via SQLite)
- [ ] No new PHPStan baseline entries
- [ ] No new usages of deprecated `AbstractAdditionalFieldProvider`
- [ ] Public API changes called out in the PR body (downstream extensions depend on these classes)
- [ ] Works on both supported cores (CI matrix covers ^13.4 and ^14.3)
<!-- AGENTS-GENERATED:END checklist -->

<!-- AGENTS-GENERATED:START examples -->
## Patterns to Follow
> **Prefer looking at real code in this repo over generic examples.**
> See **Golden Samples** section above for files that demonstrate correct patterns.
<!-- AGENTS-GENERATED:END examples -->

<!-- AGENTS-GENERATED:START help -->
## When stuck
- TYPO3 Scheduler API: https://docs.typo3.org/c/typo3/cms-scheduler/main/en-us/
- Core API: https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/
- Check `Tests/Fixtures/` for minimal working usages of the base classes
- Review root AGENTS.md for project-wide conventions
<!-- AGENTS-GENERATED:END help -->

<!-- AGENTS-GENERATED:START skill-reference -->
## Skill Reference
> For TYPO3 extension standards, TER compliance, and conformance checks:
> **Invoke skill:** `typo3-conformance`
<!-- AGENTS-GENERATED:END skill-reference -->
