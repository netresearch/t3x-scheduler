# 2.0.0

## BREAKING

- Drop TYPO3 v12.4 support; the extension now requires TYPO3 ^13.4 || ^14.3
- `AbstractAdditionalFieldProvider` is deprecated and will be removed together with TYPO3 v14 support, because TYPO3 removes `\TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider` in v15.0 (use native task types with additional fields via TCA)
- `AbstractAdditionalFieldProvider::$definedFields` and `AbstractField::$value` no longer carry a declaration-time default. Both constructors assign them unconditionally, so subclasses calling `parent::__construct()` are unaffected. A subclass that overrides the constructor **without** calling the parent previously read `[]` resp. `null` and now raises `Error: Typed property ... must not be accessed before initialization`

## FEATURES

- Add TYPO3 v14.3 support

## MISC

- Replace the removed `TYPO3\CMS\Scheduler\Task\Enumeration\Action` with the native `TYPO3\CMS\Scheduler\SchedulerManagementAction` enum
- Send reporting mails via `MailerInterface::send()`; `MailMessage::send()` was removed in TYPO3 v14
- Add a unit and functional test suite based on typo3/testing-framework
- Extend the CI matrix to PHP 8.2-8.5 x TYPO3 13.4/14.3 and enable functional tests


# 1.1.8

## MISC

- Remove strict_types from ext_emconf.php


# 1.1.7

## MISC

- 8b9bf92 Fix phpstan issues, add fractor

## Contributors

- Rico Sonntag

# 1.1.5

## MISC

- 521684b Allow objects as value in field configuration
- 411778a Use constant instead of string value
- 1013e70 Update actions/checkout action to v5
- c4622cd Add renovate.json

## Contributors

- Rico Sonntag
- renovate[bot]

# 1.1.4

## MISC

- 777aded Update dev tools
- 335c88b Fix phpstan-baseline command
- f47098e Add github ci configuration

## Contributors

- Rico Sonntag

# 1.1.3

## MISC

- 5bf7eb9 Update README

## Contributors

- Rico Sonntag

# 1.1.2

## MISC

- 3018cdc Add CI test for PHP 81

## Contributors

- Rico Sonntag

# 1.1.1

## MISC

- ed8d3b6 Update code analysis scripts

## Contributors

- Rico Sonntag

# 1.1.0

## MISC

- 7ccc67d Add MultiSelect field

## Contributors

- Rico Sonntag

# 1.0.1

## MISC

- 1532ab1 Update rector configuration
- 6811d8a Update phpstan configuration

## Contributors

- Rico Sonntag

# 1.0.0

## MISC

- 9795f8b Initial commit

## Contributors

- Rico Sonntag

