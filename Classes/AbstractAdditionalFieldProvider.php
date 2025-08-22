<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler;

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
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

/**
 * Abstract additional field provider.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
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
    protected array $definedFields = [];

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
     * @param array                     $taskInfo        Array with the task information
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

            /** @var AbstractField $field */
            $field = GeneralUtility::makeInstance(
                $config['type'],
                $identifier,
                $this->getLabel($key, $config['translationFile'])
            );

            $field->setValue(
                $this->getFieldValue($key, $taskInfo, $task, $schedulerModule)
            );

            $field->setDescription(
                $this->getLabel(
                    $key . '.description',
                    $config['translationFile'],
                    [],
                    true
                )
            );

            if ($field instanceof SelectField) {
                $field->setOptions($config['options']);
            }

            $additionalFields[$identifier] = $field->getAdditionalField();
        }

        return $additionalFields;
    }

    /**
     * Validates the Additional fields.
     *
     * @param array                     $submittedData   Array with Submitted data
     * @param SchedulerModuleController $schedulerModule Parent object context
     *
     * @return bool
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        foreach ($this->definedFields as $key => $field) {
            if ($field['validators'] === []) {
                continue;
            }

            foreach ($field['validators'] as $validatorClass) {
                $value    = null;
                $fieldKey = $this->getFieldKey($key);

                if (isset($submittedData[$fieldKey])) {
                    $value = trim((string) $submittedData[$fieldKey]);
                }

                /** @var AbstractValidator $validator */
                $validator = GeneralUtility::makeInstance(
                    $validatorClass,
                    $value,
                    $key
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
     * @param array        $submittedData Data submitted by the form
     * @param AbstractTask $task          TaskObject to save the data to
     *
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        /** @var \Netresearch\NrScheduler\AbstractTask $task */
        $task->enableReporting  = (bool) $submittedData['enableReporting'];
        $task->reportingEmails  = $submittedData['reportingEmails'];
        $task->reportingSubject = $submittedData['reportingSubject'];
        $task->reportingMessage = $submittedData['reportingMessage'];
        $task->environment      = $submittedData['environment'];
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
     * @param array                     $taskInfo        Array with the task information
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
            if ($schedulerModule->getCurrentAction()->equals(Action::EDIT)) {
                $taskInfo[$fieldIdentifier] = '';

                if ($task instanceof AbstractTask) {
                    $getter = 'get' . ucfirst($name);

                    $taskInfo[$fieldIdentifier] = method_exists($task, $getter) ? $task->{$getter}() : $task->{$name};
                }
            } else {
                $taskInfo[$fieldIdentifier] = $this->definedFields[$name]['default'];
            }
        }

        return $taskInfo[$fieldIdentifier];
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
        return $GLOBALS['LANG'];
    }
}
