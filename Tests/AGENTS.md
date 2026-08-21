<!-- Managed by agent: keep sections and order; edit content, not structure. Last updated: 2026-08-19 -->

# AGENTS.md — Tests

<!-- AGENTS-GENERATED:START overview -->
## Overview
Unit and functional test suites for the `nr_scheduler` base classes, built on `typo3/testing-framework`. **Use the `typo3-testing` skill** for comprehensive guidance.
<!-- AGENTS-GENERATED:END overview -->

<!-- AGENTS-GENERATED:START filemap -->
## Key Files
| File | Purpose |
|------|---------|
| `Fixtures/DummyTask.php` | Minimal concrete `AbstractTask` used by functional tests |
| `Fixtures/DummyAdditionalFieldProvider.php` | Concrete field provider exercising `Fields/` and `Validators/` |
| `Fixtures/RejectingValidator.php` | Validator that always rejects, for negative-path tests |
| `Functional/AbstractTaskTest.php` | Task execution, environment gating, mail failure reporting |
| `Functional/ExtensionIntegrationTest.php` | Extension loads cleanly into a TYPO3 instance |
| `Unit/Fields/FieldRenderingTest.php` | HTML rendering of every field type |
<!-- AGENTS-GENERATED:END filemap -->

<!-- AGENTS-GENERATED:START golden-samples -->
## Golden Samples (follow these patterns)
| Pattern | Reference |
|---------|-----------|
| Functional test | `Functional/AbstractTaskTest.php` |
| Unit test with data providers | `Unit/Fields/FieldRenderingTest.php` |
<!-- AGENTS-GENERATED:END golden-samples -->

<!-- AGENTS-GENERATED:START structure -->
## Test Structure
```
Tests/
├── Unit/           → fast, isolated tests (UnitTestCase); config Build/phpunit.xml
├── Functional/     → tests booting TYPO3 (FunctionalTestCase); config Build/FunctionalTests.xml
└── Fixtures/       → dummy task, field provider, validator shared by both suites
```
PHPUnit configs live in `Build/`, not here: `Build/phpunit.xml` and `Build/FunctionalTests.xml`, the locations the shared runner finds without a conf. The suites are `unit` and `functional`.
<!-- AGENTS-GENERATED:END structure -->

## Setup
- `composer install` (binaries land in `.Build/bin/`, configured via composer.json `config`)
- Functional tests boot a real TYPO3: with no MySQL/MariaDB available, set `typo3DatabaseDriver=pdo_sqlite` (CI does the same)
- To test against the other supported core locally: `composer update --with "typo3/cms-core:^13.4" --with "typo3/cms-fluid:^13.4" --with "typo3/cms-scheduler:^13.4"`

<!-- AGENTS-GENERATED:START commands -->
## Running Tests
| Type | Command |
|------|---------|
| Unit tests | `composer ci:test:php:unit` |
| Functional tests | `typo3DatabaseDriver=pdo_sqlite composer ci:test:php:functional` |
| Unit coverage (HTML) | `composer ci:test:php:unit:coverage` → `.Build/coverage/` |
| Single file | `.Build/bin/phpunit --configuration Build/phpunit.xml Tests/Unit/Fields/FieldRenderingTest.php` |

Functional tests need a database; without MySQL/MariaDB use the SQLite driver env var above (CI does the same).
<!-- AGENTS-GENERATED:END commands -->

<!-- AGENTS-GENERATED:START patterns -->
## Key Patterns (TYPO3-specific)
- Unit tests extend `\TYPO3\TestingFramework\Core\Unit\UnitTestCase`
- Functional tests extend `\TYPO3\TestingFramework\Core\Functional\FunctionalTestCase` and declare `$coreExtensionsToLoad = ['scheduler']` plus `$testExtensionsToLoad` pointing at this extension
- Data providers use PHPUnit attributes (`#[DataProvider('...')]`), not annotations
- Fixture classes live in `Tests/Fixtures/`, autoloaded via `Netresearch\NrScheduler\Tests\`
<!-- AGENTS-GENERATED:END patterns -->

<!-- AGENTS-GENERATED:START code-style -->
## Code Style
- Test class name matches source: `MyClass` → `MyClassTest`, marked `final`
- One assertion concept per test; data providers for similar cases
- Same php-cs-fixer/PHPStan rules as `Classes/` — test code is analyzed too
- No real HTTP calls, no shared state between tests
<!-- AGENTS-GENERATED:END code-style -->

## Security
- Fixtures must not contain real credentials or hostnames — dummy values only
- Negative paths are part of coverage: use `Fixtures/RejectingValidator.php` to assert validation failures

<!-- AGENTS-GENERATED:START checklist -->
## PR Checklist
- [ ] `composer ci:test:php:unit` and functional suite (SQLite) pass, output pasted
- [ ] New functionality has tests; regressions get a failing-first test
- [ ] Fixtures are minimal and focused
- [ ] PHPStan clean on the final tree including new test files
<!-- AGENTS-GENERATED:END checklist -->

## Patterns to Follow
> **Prefer looking at real code in this repo over generic examples.**
> See **Golden Samples** above; `Fixtures/` holds the minimal concrete implementations the suites exercise.

## When stuck
- typo3/testing-framework docs: https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/Testing/Index.html
- Compare with the existing functional tests before inventing new setup
- Review root AGENTS.md for project-wide conventions

<!-- AGENTS-GENERATED:START skill-reference -->
## Skill Reference
> For comprehensive TYPO3 testing guidance including fixtures, mocking, and CI setup:
> **Invoke skill:** `typo3-testing`
<!-- AGENTS-GENERATED:END skill-reference -->
