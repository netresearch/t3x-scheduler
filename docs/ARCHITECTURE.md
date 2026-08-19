# Architecture

Agent-facing component map of `netresearch/nr-scheduler` (extension key `nr_scheduler`). Facts verified against the tree on 2026-08-19; when in doubt, the code wins.

## System Overview

The extension is a library, not an application: it ships abstract base classes that other Netresearch TYPO3 extensions extend to build scheduler tasks with typed, validated additional fields. There are no controllers, routes, database tables, or frontend plugins. Everything runs inside the TYPO3 backend scheduler module context on TYPO3 ^13.4 / ^14.3, PHP ^8.2.

## Components

| Component | Path | Role |
|-----------|------|------|
| Task base | `Classes/AbstractTask.php` | Extends `\TYPO3\CMS\Scheduler\Task\AbstractTask`; adds flash messages, translation (traits) and mail-based failure reporting |
| Field provider (deprecated) | `Classes/AbstractAdditionalFieldProvider.php` | Wraps the TYPO3 scheduler field-provider API removed in v15; `@deprecated` since 2.0.0 |
| Field types | `Classes/Fields/` | `AbstractField` + Text, TextArea, Select, MultiSelect, CheckBox, Password; render backend form HTML via Fluid `TagBuilder` |
| Validators | `Classes/Validators/AbstractValidator.php` | Base for per-field input validation |
| Traits | `Classes/Traits/` | `FlashMessageTrait` (backend notifications), `TranslationTrait` (XLF labels) |
| Exception | `Classes/Exception.php` | Extension-specific exception type |
| DI config | `Configuration/Services.yaml` | Autowire + autoconfigure for `Netresearch\NrScheduler\` |
| Translations | `Resources/Private/Language/` | `Resources/Private/Language/locallang.xlf` + German de.locallang.xlf variant |
| Tests | `Tests/` | Unit (field rendering) + functional (task, field provider, integration) on typo3/testing-framework; see `Tests/AGENTS.md` |
| Tooling | `Build/` | phpstan/rector/fractor/php-cs-fixer configs, PHPUnit XMLs, `Scripts/` (tag-version check, harness verify) |

## Data Flow

1. A consuming extension subclasses `AbstractTask` (and, legacy-only, `AbstractAdditionalFieldProvider`) and registers the task with TYPO3's scheduler.
2. When the backend renders the task form, field objects from `Classes/Fields/` produce the input HTML; on save, validators from `Classes/Validators/` accept or reject the submitted values.
3. At run time the scheduler executes the task; `FlashMessageTrait`/`TranslationTrait` supply user-visible messages and labels.

## Dependency Rules

Derived from the tree (no phpat/architecture test suite exists):

- `Classes/` depends only on TYPO3 core, scheduler, and Fluid APIs — no third-party runtime packages (`composer.json` `require`).
- `Tests/` may depend on `Classes/` and `typo3/testing-framework`; never the reverse.
- New code must not depend on `AbstractAdditionalFieldProvider` (deprecated, removed with TYPO3 v15 support).

## Key Decisions

- Deprecation path for the field-provider API: see the notice in `README.md` and the `@deprecated` docblock in `Classes/AbstractAdditionalFieldProvider.php`.
- CI strategy (centralized reusables, drift-enforced checks file): documented inline in `.github/workflows/checks.yml` and `.github/workflows/AGENTS.md`.
- Release flow: tag-driven via `.github/workflows/release.yml`. `Build/Scripts/check-tag-version.sh` compares semver tags at HEAD with the `ext_emconf.php` version; it is written as a CaptainHook pre-push helper, but no CaptainHook config currently wires it — run it manually before pushing a tag.

There is no ADR directory; decisions live in the files referenced above.
