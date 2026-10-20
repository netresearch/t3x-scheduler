<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 * SPDX-FileCopyrightText: Netresearch DTT GmbH
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler;

use BadMethodCallException;
use InvalidArgumentException;
use Netresearch\NrScheduler\Fields\AbstractField;
use Netresearch\NrScheduler\Fields\CheckBoxField;
use Netresearch\NrScheduler\Fields\SelectField;
use Netresearch\NrScheduler\Fields\TextAreaField;
use Netresearch\NrScheduler\Fields\TextField;
use Netresearch\NrScheduler\Traits\FlashMessageTrait;
use Netresearch\NrScheduler\Traits\TranslationTrait;
use Netresearch\NrScheduler\Validators\AbstractValidator;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider as SchedulerAbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\SchedulerManagementAction;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Abstract additional field provider.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license GPL-3.0-or-later
 *
 * @see    https://www.netresearch.de
 *
 * @deprecated since 2.0.0, will be removed together with TYPO3 v14 support. This class
 *             wraps \TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider, which TYPO3
 *             deprecated in v14 and removes in v15.0. Migrate consuming tasks to native
 *             task types with additional fields declared via TCA.
 */
abstract class AbstractAdditionalFieldProvider extends SchedulerAbstractAdditionalFieldProvider
{
    use FlashMessageTrait;
    use TranslationTrait;

    /**
     * Constants for field names. Field name must match the property name of the task.
     *
     * @var string
     */
    private const FIELD_ENABLE_REPORTING = 'enableReporting';

    /**
     * @var string
     */
    private const FIELD_REPORTING_EMAILS = 'reportingEmails';

    /**
     * @var string
     */
    private const FIELD_REPORTING_SUBJECT = 'reportingSubject';

    /**
     * @var string
     */
    private const FIELD_REPORTING_MESSAGE = 'reportingMessage';

    /**
     * @var string
     */
    private const FIELD_ENVIRONMENT = 'environment';

    /**
     * Contains the errors as a key value pair.
     * The key represents the field name and the value is the error message.
     *
     * @var array<string, string>
     */
    protected array $errors = [];

    /**
     * Array which contains the additional field definitions.
     *
     * @var array<string, array<string, bool|int|string|string[]>>
     */
    protected array $definedFields;

    /**
     * Containing the submitted data.
     *
     * @var string[]
     */
    protected array $submittedData = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->definedFields = $this->getBasicFieldConfiguration() + $this->getFieldConfiguration();
    }

    /**
     * Returns the field configuration.
     *
     * @return array<string, array<string, bool|int|string|string[]>>
     */
    abstract public function getFieldConfiguration(): array;

    /**
     * Build the additional form fields.
     *
     * @param array<mixed>              $taskInfo        Array with the task information
     * @param AbstractTask|null         $task            The task object
     * @param SchedulerModuleController $schedulerModule Parent object context
     *
     * @return string[][]
     */
    public function getAdditionalFields(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule,
    ): array {
        $additionalFields = [];

        foreach ($this->definedFields as $key => $config) {
            $identifier = $this->getFieldKey($key);

            /** @var class-string<AbstractField> $fieldClass */
            $fieldClass = $config['type'];

            $translationFile = $this->getTranslationFile($key, $config);

            /** @var AbstractField $field */
            $field = GeneralUtility::makeInstance(
                $fieldClass,
                $identifier,
                $this->getLabel($key, $translationFile),
            );

            $field->setValue(
                $this->getFieldValue($key, $taskInfo, $task, $schedulerModule),
            );

            $field->setDescription(
                $this->getLabel(
                    $key . '.description',
                    $translationFile,
                    [],
                    true,
                ),
            );

            if ($field instanceof SelectField) {
                $field->setOptions($this->getSelectOptions($key, $config));
            }

            $additionalFields[$identifier] = $field->getAdditionalField();
        }

        return $additionalFields;
    }

    /**
     * Validates the Additional fields.
     *
     * @param array<mixed>              $submittedData   Array with Submitted data
     * @param SchedulerModuleController $schedulerModule Parent object context
     *
     * @return bool
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        foreach ($this->definedFields as $key => $field) {
            // A non-array value was skipped by foreach before, with a warning.
            if (!is_array($field['validators']) || $field['validators'] === []) {
                continue;
            }

            foreach ($field['validators'] as $validatorClass) {
                $value    = null;
                $fieldKey = $this->getFieldKey($key);

                if (isset($submittedData[$fieldKey]) && is_scalar($submittedData[$fieldKey])) {
                    $value = trim((string) $submittedData[$fieldKey]);
                }

                /** @var class-string<AbstractValidator> $validatorClassName */
                $validatorClassName = $validatorClass;

                /** @var AbstractValidator $validator */
                $validator = GeneralUtility::makeInstance(
                    $validatorClassName,
                    $value,
                    $key,
                );

                if (!$validator->validate()) {
                    $this->errors[$key] = $validator->getErrorMessage();
                }
            }
        }

        if ($this->errors === []) {
            return true;
        }

        foreach ($this->errors as $error) {
            $this->addErrorMessage($error);
        }

        return false;
    }

    /**
     * Saves the data of additional fields.
     *
     * @param array<mixed> $submittedData Data submitted by the form
     * @param AbstractTask $task          TaskObject to save the data to
     *
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        /** @var \Netresearch\NrScheduler\AbstractTask $task */
        $task->enableReporting  = (bool) $submittedData['enableReporting'];
        $task->reportingEmails  = $this->getSubmittedString($submittedData, 'reportingEmails');
        $task->reportingSubject = $this->getSubmittedString($submittedData, 'reportingSubject');
        $task->reportingMessage = $this->getSubmittedString($submittedData, 'reportingMessage');
        $task->environment      = $this->getSubmittedString($submittedData, 'environment');
    }

    /**
     * Returns a submitted value that is assigned to a string property of the task.
     *
     * Anything but a string failed before as a TypeError on the typed property.
     *
     * @param array<mixed> $submittedData Data submitted by the form
     * @param string       $name          Field name
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function getSubmittedString(array $submittedData, string $name): string
    {
        $value = $submittedData[$name] ?? null;

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf('The submitted value of field "%s" must be a string, %s given.', $name, get_debug_type($value)),
                1792371351,
            );
        }

        return $value;
    }

    /**
     * Returns the translation file of a field configuration.
     *
     * Anything but a string failed before as a TypeError in getLabel().
     *
     * @param string                                  $name   Field name
     * @param array<string, bool|int|string|string[]> $config Field configuration
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function getTranslationFile(string $name, array $config): string
    {
        $translationFile = $config['translationFile'] ?? null;

        if (!is_string($translationFile)) {
            throw new InvalidArgumentException(
                sprintf('The "translationFile" of field "%s" must be a string, %s given.', $name, get_debug_type($translationFile)),
                1792371352,
            );
        }

        return $translationFile;
    }

    /**
     * Returns the options of a select field configuration.
     *
     * A non-array failed before as a TypeError in SelectField::setOptions().
     * Integer keys stay integer keys, as PHP stores numeric string keys as
     * integers.
     *
     * @param string                                  $name   Field name
     * @param array<string, bool|int|string|string[]> $config Field configuration
     *
     * @return array<string, string>
     *
     * @throws InvalidArgumentException
     */
    private function getSelectOptions(string $name, array $config): array
    {
        $configuredOptions = $config['options'] ?? null;

        if (!is_array($configuredOptions)) {
            throw new InvalidArgumentException(
                sprintf('The "options" of field "%s" must be an array, %s given.', $name, get_debug_type($configuredOptions)),
                1792371353,
            );
        }

        $options = [];

        foreach ($configuredOptions as $value => $label) {
            $options[(string) $value] = $label;
        }

        return $options;
    }

    /**
     * Return the configuration of the basic fields.
     *
     * @return array<string, array<string, bool|int|string|string[]>>
     */
    protected function getBasicFieldConfiguration(): array
    {
        return [
            self::FIELD_ENABLE_REPORTING => [
                'default'         => false,
                'type'            => CheckBoxField::class,
                'validators'      => [],
                'translationFile' => '',
            ],
            self::FIELD_REPORTING_EMAILS => [
                'default'         => '',
                'type'            => TextField::class,
                'validators'      => [],
                'translationFile' => '',
            ],
            self::FIELD_REPORTING_SUBJECT => [
                'default'         => '',
                'type'            => TextField::class,
                'validators'      => [],
                'translationFile' => '',
            ],
            self::FIELD_REPORTING_MESSAGE => [
                'default'         => '',
                'type'            => TextAreaField::class,
                'validators'      => [],
                'translationFile' => '',
            ],
            self::FIELD_ENVIRONMENT => [
                'default'         => '',
                'type'            => TextField::class,
                'validators'      => [],
                'translationFile' => '',
            ],
        ];
    }

    /**
     * Get the value for a field.
     *
     * @param string                    $name            Field identifier
     * @param array<mixed>              $taskInfo        Array with the task information
     * @param AbstractTask|null         $task            The task object
     * @param SchedulerModuleController $schedulerModule Parent object context
     *
     * @return bool|int|float|string|int[]|string[]|object|null
     */
    protected function getFieldValue(
        string $name,
        array &$taskInfo,
        ?AbstractTask $task,
        SchedulerModuleController $schedulerModule,
    ): array|bool|int|float|string|object|null {
        $fieldIdentifier = $this->getFieldKey($name);

        if (($taskInfo[$fieldIdentifier] ?? null) === null) {
            if ($schedulerModule->getCurrentAction() === SchedulerManagementAction::EDIT) {
                $taskInfo[$fieldIdentifier] = '';

                if ($task instanceof AbstractTask) {
                    $getter = 'get' . ucfirst($name);

                    if (method_exists($task, $getter)) {
                        $callable = [$task, $getter];

                        // A getter that is not public failed before in call_user_func().
                        if (!is_callable($callable)) {
                            throw new BadMethodCallException(
                                sprintf('The getter %s::%s() is not callable.', $task::class, $getter),
                                1792371354,
                            );
                        }

                        $taskInfo[$fieldIdentifier] = $callable();
                    } elseif (property_exists($task, $name)) {
                        /** @var mixed $value */
                        $value                      = $task->{$name};
                        $taskInfo[$fieldIdentifier] = $value;
                    }
                }
            } else {
                $taskInfo[$fieldIdentifier] = $this->definedFields[$name]['default'];
            }
        }

        return $this->narrowFieldValue($name, $taskInfo[$fieldIdentifier]);
    }

    /**
     * Narrows a field value to the types a field accepts.
     *
     * A resource failed before as a TypeError in AbstractField::setValue().
     *
     * @param string $name  Field name
     * @param mixed  $value Field value
     *
     * @return bool|int|float|string|int[]|string[]|object|null
     *
     * @throws InvalidArgumentException
     */
    private function narrowFieldValue(string $name, mixed $value): array|bool|int|float|string|object|null
    {
        if ($value === null || is_scalar($value) || is_object($value)) {
            return $value;
        }

        if (is_array($value)) {
            $values = [];

            foreach ($value as $key => $item) {
                if (!is_int($item) && !is_string($item)) {
                    throw new InvalidArgumentException(
                        sprintf('The value of field "%s" must hold integers or strings only, %s given.', $name, get_debug_type($item)),
                        1792371355,
                    );
                }

                $values[$key] = $item;
            }

            return $values;
        }

        throw new InvalidArgumentException(
            sprintf('The value of field "%s" has the unsupported type %s.', $name, get_debug_type($value)),
            1792371356,
        );
    }

    /**
     * Returns the identifier for a field.
     *
     * @param string $fieldName Name of field
     *
     * @return string
     */
    protected function getFieldKey(string $fieldName): string
    {
        return $fieldName;
    }

    /**
     * Returns an instance of the language service.
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        $languageService = $GLOBALS['LANG'];
        assert($languageService instanceof LanguageService);

        return $languageService;
    }
}
