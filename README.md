<!-- SPDX-License-Identifier: GPL-3.0-or-later -->
<!-- SPDX-FileCopyrightText: Netresearch DTT GmbH -->
[![Latest version](https://img.shields.io/github/v/release/netresearch/t3x-scheduler?sort=semver)](https://github.com/netresearch/t3x-scheduler/releases/latest)
[![License](https://img.shields.io/github/license/netresearch/t3x-scheduler)](https://github.com/netresearch/t3x-scheduler/blob/main/LICENSE)
[![CI](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml/badge.svg)](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml)

# Scheduler Extensions for TYPO3

This extension extends the TYPO3 scheduler extension with some functions.


## Requirements

| Extension | TYPO3           | PHP     |
|-----------|-----------------|---------|
| 2.x       | 13.4 LTS, 14.3 LTS | 8.2-8.5 |
| 1.x       | 12.4 LTS        | 8.2+    |

`Netresearch\NrScheduler\AbstractAdditionalFieldProvider` is deprecated as of 2.0.0. It
wraps `\TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider`, which TYPO3 removes in
v15.0; migrate consuming tasks to native task types with additional fields via TCA.


## Installation

### Composer
``composer require netresearch/nr-scheduler``

### GIT
``git clone git@github.com:netresearch/t3x-scheduler.git``


## Development
### Testing
```bash
composer install

composer ci:cgl
composer ci:test
composer ci:test:php:lint
composer ci:test:php:phpstan
composer ci:test:php:rector
composer ci:test:php:fractor
composer ci:test:php:unit
composer ci:test:php:functional
```

Functional tests need a database. Without a MySQL/MariaDB service, run them against
SQLite:

```bash
typo3DatabaseDriver=pdo_sqlite composer ci:test:php:functional
```

To verify the other supported core version locally:

```bash
composer update --with "typo3/cms-core:^13.4" --with "typo3/cms-fluid:^13.4" --with "typo3/cms-scheduler:^13.4"
```


## Security

Report vulnerabilities as described in [SECURITY.md](SECURITY.md). What the extension does and does not protect, its trust boundaries and how common weaknesses are countered are described in [docs/SECURITY-ASSURANCE.md](https://github.com/netresearch/t3x-scheduler/blob/main/docs/SECURITY-ASSURANCE.md).


## Governance and policies

This extension follows the organisation-wide Netresearch policies:

- [Governance](https://github.com/netresearch/.github/blob/main/GOVERNANCE.md): ownership, roles, how decisions are made and conflicts resolved.
- [Roadmap](https://github.com/netresearch/.github/blob/main/ROADMAP.md): planned and excluded work for the next twelve months.
- [Handling of dependency and code analysis findings](https://github.com/netresearch/.github/blob/main/SECURITY.md#handling-of-dependency-and-code-analysis-findings): which vulnerability, licence and static-analysis findings must be fixed, by when, and how exceptions are recorded.
- [Secret management](https://github.com/netresearch/.github/blob/main/SECURITY.md#secret-management): where CI and release credentials are stored, who may use them, how committed secrets are detected, and when secrets are rotated.
- [Access roster](https://github.com/netresearch/.github/blob/main/docs/access-roster.md): the people and teams with administrative or write access to this repository.

Checks that the workflows in `.github/workflows/` run on pull requests:

- `.github/workflows/checks.yml`: Composer Audit (fails on any advisory for an installed package; `composer.json` records one exception under `config.audit.ignore`, `PKSA-y2cr-5h3j-g3ys` in `firebase/php-jwt` via `typo3/cms-core`) and Opengrep SAST (which findings block is set organisation-wide, see [Static analysis (SAST)](https://github.com/netresearch/.github/blob/main/SECURITY.md#static-analysis-sast)), both through `typo3-ci-workflows`' `security.yml`; Dependency Review (fails on newly added dependencies with a vulnerability of severity high or higher); PHP License Audit (`license-check.yml`, fails on an SSPL or BSL licensed Composer dependency); CodeQL with language auto-detection, which finds no JavaScript or Go here and analyses the workflow files (CodeQL has no PHP analysis; PHPStan and Opengrep cover the PHP code); Betterleaks secret scanning; zizmor for the workflow files (the job uploads its findings to code scanning; the `zizmor` code-scanning check is a required check on `main`); the pull request quality check (`pr-quality`, on non-draft pull requests only: its Quality Gate job reports the size of the change and warns on large pull requests, its Auto-Approve job approves pull requests opened from this repository by authors GitHub associates with it as owner, member or collaborator); and the aggregate gate `All security checks`, which fails when one of these jobs fails or is cancelled. The fuzz job is skipped because `Build/phpunit.xml` defines no `Fuzz` test suite. The OpenSSF Scorecard job runs only on pushes to `main` and on the weekly schedule.
- `.github/workflows/ci.yml`: PHP lint on PHP 8.2 to 8.5; code style (PHP-CS-Fixer, `Build/.php-cs-fixer.dist.php`), Rector (`Build/rector.php`) and Fractor (`Build/fractor.php`) on PHP 8.2; PHPStan (level 6 with the baseline `Build/phpstan-baseline.neon`, `Build/phpstan.neon`), unit tests and functional tests (SQLite) on PHP 8.2 to 8.5 with TYPO3 ^13.4 and ^14.3; an advisory PHPStan pass against the PHPUnit the matrix resolves without the version cap (`PHPStan (unpinned PHPUnit)`); and the aggregate gate `All CI checks`. There is no `Documentation/` directory to render.
- `.github/workflows/dco.yml` requires a DCO sign-off on every commit of the pull request except merge commits and commits by `dependabot[bot]`, `renovate[bot]` and `github-actions[bot]`, and `.github/workflows/harness-verify.yml` runs `Build/Scripts/verify-harness.sh`.
- `.github/workflows/labeler.yml` labels the pull request by the paths it changes; `.github/workflows/community.yml` greets a contributor on their first pull request; `.github/workflows/auto-merge-deps.yml` approves and enables auto-merge for Dependabot and Renovate pull requests that carry neither the `deps-no-automerge` nor the `deps-major` label, and is skipped for all others.

- `.github/workflows/check-template-drift.yml`: Template drift fails when a file governed by the `typo3-extension` template of `netresearch/.github` differs from it; `.github/template.yaml` records the template and the intentional drift (`ci.yml` and `release.yml`).

The reusable workflows also run helper jobs that decide which of their jobs apply, for example `Preflight (event gate)`, `Detect Documentation` and CodeQL's `Prepare languages`.
