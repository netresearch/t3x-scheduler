[![Latest version](https://img.shields.io/github/v/release/netresearch/t3x-scheduler?sort=semver)](https://github.com/netresearch/t3x-scheduler/releases/latest)
[![License](https://img.shields.io/github/license/netresearch/t3x-scheduler)](https://github.com/netresearch/t3x-scheduler/blob/main/LICENSE)
[![CI](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml/badge.svg)](https://github.com/netresearch/t3x-scheduler/actions/workflows/ci.yml)

# TYPO3 Extension nr-scheduler

This extension extends the TYPO3 scheduler extension with some functions.


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
composer ci:test:php:phplint
composer ci:test:php:phpstan
composer ci:test:php:rector
```
