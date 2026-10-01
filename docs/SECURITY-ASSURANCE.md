<!-- SPDX-License-Identifier: GPL-3.0-or-later -->
<!-- SPDX-FileCopyrightText: Netresearch DTT GmbH -->

# Security assurance

What users of `netresearch/nr-scheduler` (extension key `nr_scheduler`) can and cannot expect in terms of security, and the argument for why the code meets those expectations. The component map is in [`ARCHITECTURE.md`](ARCHITECTURE.md); reporting a vulnerability is described in [`SECURITY.md`](../SECURITY.md). Facts were checked against the tree and against TYPO3 13.4.35 and 14.3.7 on 2026-09-30; when in doubt, the code wins.

## What the extension is

A library of base classes for TYPO3 scheduler tasks. It has no controllers, routes, database tables, frontend plugins or console commands of its own. A consuming extension subclasses `Classes/AbstractTask.php` (and, on the deprecated path, `Classes/AbstractAdditionalFieldProvider.php`), registers the task with TYPO3's scheduler and implements `executeTask()`. The extension adds to every such task:

- five settings: `enableReporting`, `reportingEmails`, `reportingSubject`, `reportingMessage` and `environment` (`AbstractTask.php`);
- a check that skips the run when the current application context is not listed in `environment` (`AbstractTask::isRunnableInContext()`);
- a plain-text mail to `reportingEmails` when `executeTask()` returns `false` or throws (`AbstractTask::sendReporting()`);
- form field classes for the scheduler backend form (`Classes/Fields/`) and a validator base class (`Classes/Validators/AbstractValidator.php`).

## Actors

| Actor | What they can do | Where it is decided |
|-------|------------------|---------------------|
| TYPO3 administrator | Create, edit, enable, disable and run tasks in the backend Scheduler module; this is the only way to set the five settings above and the fields a consuming extension defines | TYPO3 core: the module is registered with `'access' => 'admin'` in `EXT:scheduler/Configuration/Backend/Modules.php` on 13.4 and 14.3; on 14.3 the `tx_scheduler_task` table is also `adminOnly` (`EXT:scheduler/Configuration/TCA/tx_scheduler_task.php`) |
| Operator with shell access | Run due tasks with `vendor/bin/typo3 scheduler:run` (usually from cron) or selected tasks with `scheduler:execute` | TYPO3 core console commands (`EXT:scheduler/Classes/Command/`) |
| Developer of a consuming extension | Decides what a task does, which extra fields it has, their types, select options and validators | Code in the consuming extension (`getFieldConfiguration()`, `executeTask()`) |
| Mail recipient | Receives the failure report | `reportingEmails`, set by an administrator |

Non-administrator backend users, frontend visitors and anonymous HTTP clients have no path into this code: it handles no requests of its own.

## What tasks execute, and how arguments are passed

- **What runs.** The work of a task is the consuming extension's `executeTask()`. This extension starts no processes and runs no commands: `Classes/` contains no call to `exec`, `shell_exec`, `system`, `passthru`, `proc_open`, `popen`, `eval` or `unserialize`, and no HTTP client. The only side effects of its own are the failure mail (`AbstractTask::sendEmail()`, through TYPO3's `MailerInterface` with the sender from `MailUtility::getSystemFrom()`) and flash messages (`Classes/Traits/FlashMessageTrait.php`, printed to standard output when running on the CLI).
- **Where the arguments come from.** Task settings are not command-line arguments. An administrator enters them in the scheduler form; the browser posts them as `tx_scheduler[<field>]` (`AbstractField::getFieldName()`, `MultiSelectField::getFieldName()`). TYPO3 core hands the submitted values to `AbstractAdditionalFieldProvider::validateAdditionalFields()`, which runs the validators the field configuration names on a trimmed copy of each posted value; fields without validators, which includes the five basic fields, are not checked. `saveAdditionalFields()` then assigns the basic values, as posted and not trimmed, to the public properties of the task, and TYPO3 core stores the task in `tx_scheduler_task`. At run time core restores the task and calls `execute()`, which reads those properties.
- **How the values are used.** `environment` is split on commas and compared strictly with the current application context (`in_array(..., true)`). `reportingEmails` is split on commas and passed as recipient list to Symfony's `MailMessage::setTo()`; `reportingSubject` and `reportingMessage` become the subject and part of the plain-text body. No value is used to build a file path, a database query, a shell command or a class name.

## Security expectations

Users can expect:

- Only TYPO3 administrators can create or change a task's settings, and only administrators or operators with shell access can run tasks; the extension widens neither (see Actors).
- Values rendered into the scheduler form are HTML-escaped: attribute values by Fluid's `TagBuilder::addAttribute()` (`htmlspecialchars()` by default, `typo3fluid/fluid` 4.6.1 and 5.3.2), the content of a textarea by `TextAreaField::getFieldHtml()` (`Tests/Unit/Fields/FieldRenderingTest.php`, `textAreaFieldEscapesMarkupInItsContent`).
- A task whose `environment` does not contain the current application context does nothing and reports success (`AbstractTaskTest::executeSkipsTheTaskAndReportsSuccessWhenTheContextDoesNotMatch`).
- A failing task is not hidden by the reporting: the task's exception is rethrown after the report is sent. A mail transport failure becomes a `Netresearch\NrScheduler\Exception` that carries the transport exception; when the task had failed as well, that exception replaces the task's, which is then not attached (`Classes/AbstractTask.php`, `execute()` and `sendReporting()`; `AbstractTaskTest::executeRethrowsTheOriginalExceptionWhenReportingIsDisabled`, `executeReportsTheOriginalExceptionMessageBeforeRethrowing`, `executeWrapsMailTransportFailuresIntoAnExtensionException`).

Users cannot expect:

- **Secrecy of `PasswordField` values.** `PasswordField` renders an `<input type="password">`, which masks the value on screen, but the stored value is written into the `value` attribute of the form, so it is in the page source of every administrator who opens the task. It is stored like every other task setting, unencrypted, in `tx_scheduler_task`. Tasks that need a real secret should read it from a secret store or from the environment instead.
- **Validation of the basic fields.** `reportingEmails`, `reportingSubject`, `reportingMessage` and `environment` have no validators. An invalid address is only detected when a report is sent: Symfony Mime's `Address` rejects it, and the failure surfaces as the wrapped exception described above.
- **Confidentiality of failure reports.** The mail body contains the exception message of the failed task (`AbstractTask::getReportingContent()`). Whatever a consuming task puts into its exception messages is mailed to the configured recipients, unencrypted unless TYPO3's mail transport is configured for TLS.
- **Protection against a malicious consuming extension.** Field types, select options, validator classes and the getter names `getFieldValue()` calls (`'get' . ucfirst($name)`) come from the consuming extension's code and are trusted as such.

## Threat model and trust boundaries

| Boundary | Input that crosses it | Control |
|----------|-----------------------|---------|
| Administrator's browser → TYPO3 backend → field provider | Posted `tx_scheduler[...]` values | Access to the Scheduler module is administrator-only (TYPO3 core); fields that name validators are checked on a trimmed copy of the posted value, the stored value is the posted one (`validateAdditionalFields()`, `saveAdditionalFields()`; `AbstractAdditionalFieldProviderTest::validateAdditionalFieldsRejectsDataAndReportsTheValidatorMessage`) |
| Stored task → scheduler form | Stored settings rendered as HTML | Escaped on output (see Security expectations) |
| Stored task → mail | Recipients, subject, message, exception text | Addresses and headers are handled by Symfony Mime/Mailer through `MailMessage` and `MailerInterface`; the body is plain text (`MailMessage::text()`), not HTML |
| Consuming extension → this library | Field configuration, validators, `executeTask()` | Trusted code, installed by the site owner |
| Operator shell → `scheduler:run` / `scheduler:execute` | Task selection | TYPO3 core; whoever runs the CLI already controls the installation |

Threats considered: a lower-privileged backend user or a visitor changing a task (not possible, no path; see Actors), markup injection into the backend form through stored settings (escaped), header injection into the failure mail (handled by Symfony Mime), and a task running on the wrong system after a database copy (the `environment` context check).

## Secure design principles applied

- **Least privilege and no new entry points.** The extension registers no route, middleware, plugin, command or table (`Configuration/Services.yaml` only enables autowiring), so it adds no attack surface beyond the administrator-only Scheduler module it extends.
- **Fail visibly.** Reporting does not turn a failure into a success: `execute()` rethrows the task's exception, and a failed mail delivery raises `Netresearch\NrScheduler\Exception` with the transport error attached, in place of the task's exception when both fail.
- **Escape on output.** Form HTML is built with `TagBuilder` rather than string concatenation; the only hand-written markup is the hidden fallback input in `CheckBoxField`, whose name comes from the developer's field identifier, not from stored data. Option labels of `SelectField` and `MultiSelectField` are set as tag content without escaping; they come from the developer's field configuration, not from stored data.
- **Strict typing.** Every PHP file under `Classes/` and `Tests/` declares `strict_types=1`, properties are typed, and PHPStan runs at level 6 with strict and deprecation rules (`Build/phpstan.neon`).

## Countering common weaknesses

| Weakness (CWE / OWASP) | Counter | Evidence |
|------------------------|---------|----------|
| CWE-79 Cross-site scripting (OWASP A03 Injection) | Attribute values escaped by `TagBuilder::addAttribute()`; textarea content escaped by `TextAreaField`; select option labels are developer-supplied and not escaped | `Classes/Fields/`, `FieldRenderingTest::textAreaFieldEscapesMarkupInItsContent` |
| CWE-78 OS command injection, CWE-94 code injection | No process execution, `eval` or dynamic include in `Classes/` | `Classes/` (no such calls) |
| CWE-502 Deserialisation of untrusted data | The extension does not unserialise anything; storing and restoring the task is TYPO3 core's job | `Classes/` |
| CWE-89 SQL injection | No database access of its own | `Classes/` |
| CWE-93 / CWE-113 CRLF injection in mail headers | Recipients and subject are passed to Symfony Mime: `Address` rejects anything that is not an RFC 2822 addr-spec, and header values are encoded rather than written raw | `AbstractTask::sendEmail()`; `symfony/mime` `Address::__construct()` and `Header/AbstractHeader::tokenNeedsEncoding()`, which encodes CR and LF |
| CWE-285 Improper authorisation (OWASP A01) | No own entry points; the Scheduler module and task table are administrator-only in TYPO3 core | Actors table |
| CWE-209 Information exposure through error messages | Exception text is sent only to the configured recipients, by mail; this is a documented limitation above | `AbstractTask::getReportingContent()` |
| Vulnerable dependencies (OWASP A06) | Composer Audit, Dependency Review and the licence audit run on every pull request | `README.md`, "Governance and policies" |

## Verification

The checks that run on every pull request, and the policies for handling their findings, are listed in the README section "Governance and policies". Locally, `composer ci:test` runs lint, PHPStan, Rector, Fractor, the unit and functional tests and the code style check (functional tests need `typo3DatabaseDriver=pdo_sqlite` when no MySQL/MariaDB is available).
