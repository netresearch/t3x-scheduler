[![Latest version](https://img.shields.io/github/v/release/netresearch/t3x-scheduler?sort=semver)](https://github.com/netresearch/t3x-scheduler/releases/latest)
[![License](https://img.shields.io/github/license/netresearch/t3x-scheduler)](https://github.com/netresearch/t3x-scheduler/blob/main/LICENSE)
[![CI](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml/badge.svg)](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml)

# TYPO3 Extension nr-scheduler

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
