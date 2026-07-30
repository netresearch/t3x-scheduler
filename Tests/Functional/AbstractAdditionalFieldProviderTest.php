<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Functional;

use Netresearch\NrScheduler\Tests\Fixtures\DummyAdditionalFieldProvider;
use Netresearch\NrScheduler\Tests\Fixtures\DummyTask;
use Netresearch\NrScheduler\Tests\Fixtures\RejectingValidator;
use Netresearch\NrScheduler\Tests\Fixtures\SchedulerManagementActionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\SchedulerManagementAction;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests the abstract additional field provider, in particular the scheduler action
 * handling that changed between TYPO3 v12 (Task\Enumeration\Action value object with
 * equals()) and TYPO3 v13/v14 (native SchedulerManagementAction enum).
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 *
 * @deprecated since 2.0.0, shares the lifetime of AbstractAdditionalFieldProvider, which
 *             TYPO3 removes in v15.0. Kept only to test that class while it is supported.
 */
final class AbstractAdditionalFieldProviderTest extends FunctionalTestCase
{
    use SchedulerManagementActionTrait;

    /**
     * @var non-empty-string[]
     */
    protected array $coreExtensionsToLoad = [
        'scheduler',
    ];

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'netresearch/nr-scheduler',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        DummyAdditionalFieldProvider::$demoTextValidators = [];

        // The provider translates labels through $GLOBALS['LANG'], which the backend
        // populates at runtime but a functional test instance does not.
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['LANG']);

        parent::tearDown();
    }

    /**
     * Builds a scheduler module controller carrying the given action.
     *
     * The controller is final and only ever sets its action while dispatching a request,
     * so it is created without invoking the constructor. This keeps the assertion pointed
     * at the real core enum rather than at a test double of it.
     *
     * @param SchedulerManagementAction $action Action the controller should report
     *
     * @return SchedulerModuleController
     *
     * @throws ReflectionException
     */
    private function createSchedulerModule(SchedulerManagementAction $action): SchedulerModuleController
    {
        /** @var SchedulerModuleController $schedulerModule */
        $schedulerModule = (new ReflectionClass(SchedulerModuleController::class))
            ->newInstanceWithoutConstructor();

        $this->setSchedulerManagementAction($schedulerModule, $action);

        return $schedulerModule;
    }

    #[Test]
    public function getAdditionalFieldsReadsValuesFromTaskWhenEditingAnExistingTask(): void
    {
        $subject = new DummyAdditionalFieldProvider();

        $task                   = new DummyTask();
        $task->reportingEmails  = 'ops@example.org';
        $task->reportingSubject = 'Import failed';
        $task->enableReporting  = true;
        $task->environment      = 'Production';

        $taskInfo = [];

        $subject->getAdditionalFields(
            $taskInfo,
            $task,
            $this->createSchedulerModule(SchedulerManagementAction::EDIT),
        );

        self::assertSame('ops@example.org', $taskInfo['reportingEmails']);
        self::assertSame('Import failed', $taskInfo['reportingSubject']);
        self::assertTrue($taskInfo['enableReporting']);
        self::assertSame('Production', $taskInfo['environment']);
    }

    /**
     * Any action other than EDIT must fall back to the configured defaults. This pins the
     * identity comparison against SchedulerManagementAction::EDIT introduced for TYPO3 v13/v14.
     *
     * @return iterable<string, array{0: SchedulerManagementAction}>
     */
    public static function nonEditActionDataProvider(): iterable
    {
        yield 'add action' => [SchedulerManagementAction::ADD];
        yield 'list action' => [SchedulerManagementAction::LIST];
    }

    #[Test]
    #[DataProvider('nonEditActionDataProvider')]
    public function getAdditionalFieldsUsesDefaultsForEveryActionButEdit(SchedulerManagementAction $action): void
    {
        $subject = new DummyAdditionalFieldProvider();

        $task                  = new DummyTask();
        $task->reportingEmails = 'ops@example.org';

        $taskInfo = [];

        $subject->getAdditionalFields($taskInfo, $task, $this->createSchedulerModule($action));

        self::assertSame('', $taskInfo['reportingEmails']);
        self::assertFalse($taskInfo['enableReporting']);
        self::assertSame('the-default', $taskInfo['demoText']);
        self::assertSame('a', $taskInfo['demoSelect']);
    }

    #[Test]
    public function getAdditionalFieldsRendersOneEntryPerConfiguredField(): void
    {
        $subject  = new DummyAdditionalFieldProvider();
        $taskInfo = [];

        $additionalFields = $subject->getAdditionalFields(
            $taskInfo,
            null,
            $this->createSchedulerModule(SchedulerManagementAction::ADD),
        );

        self::assertSame(
            [
                'enableReporting',
                'reportingEmails',
                'reportingSubject',
                'reportingMessage',
                'environment',
                'demoText',
                'demoSelect',
            ],
            array_keys($additionalFields),
        );

        self::assertSame('checkToggle', $additionalFields['enableReporting']['type']);
        self::assertSame('select', $additionalFields['demoSelect']['type']);
        self::assertStringContainsString('name="tx_scheduler[demoText]"', $additionalFields['demoText']['code']);
        self::assertStringContainsString('value="the-default"', $additionalFields['demoText']['code']);
        self::assertStringContainsString('<option', $additionalFields['demoSelect']['code']);
    }

    #[Test]
    public function existingTaskInfoValuesAreNotOverwritten(): void
    {
        $subject  = new DummyAdditionalFieldProvider();
        $taskInfo = ['demoText' => 'value-from-a-failed-submit'];

        $subject->getAdditionalFields(
            $taskInfo,
            null,
            $this->createSchedulerModule(SchedulerManagementAction::ADD),
        );

        self::assertSame('value-from-a-failed-submit', $taskInfo['demoText']);
    }

    #[Test]
    public function validateAdditionalFieldsAcceptsDataWhenNoValidatorIsConfigured(): void
    {
        $subject       = new DummyAdditionalFieldProvider();
        $submittedData = ['demoText' => 'anything'];

        self::assertTrue(
            $subject->validateAdditionalFields(
                $submittedData,
                $this->createSchedulerModule(SchedulerManagementAction::ADD),
            ),
        );
    }

    #[Test]
    public function validateAdditionalFieldsRejectsDataAndReportsTheValidatorMessage(): void
    {
        DummyAdditionalFieldProvider::$demoTextValidators = [RejectingValidator::class];

        $subject       = new DummyAdditionalFieldProvider();
        $submittedData = ['demoText' => 'anything'];

        // Flash messages are echoed on CLI by FlashMessageTrait.
        $this->expectOutputRegex('/Field "demoText" rejected value "anything"\./');

        self::assertFalse(
            $subject->validateAdditionalFields(
                $submittedData,
                $this->createSchedulerModule(SchedulerManagementAction::ADD),
            ),
        );
    }

    #[Test]
    public function saveAdditionalFieldsTransfersTheBasicFieldsOntoTheTask(): void
    {
        $subject = new DummyAdditionalFieldProvider();
        $task    = new DummyTask();

        $subject->saveAdditionalFields(
            [
                'enableReporting'  => '1',
                'reportingEmails'  => 'ops@example.org, dev@example.org',
                'reportingSubject' => 'Nightly import failed',
                'reportingMessage' => 'Please check the import log.',
                'environment'      => 'Production,Staging',
            ],
            $task,
        );

        self::assertTrue($task->enableReporting);
        self::assertSame('ops@example.org, dev@example.org', $task->reportingEmails);
        self::assertSame('Nightly import failed', $task->reportingSubject);
        self::assertSame('Please check the import log.', $task->reportingMessage);
        self::assertSame('Production,Staging', $task->environment);
    }
}
