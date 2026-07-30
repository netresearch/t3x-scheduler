<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Functional;

use Netresearch\NrScheduler\Exception;
use Netresearch\NrScheduler\Tests\Fixtures\DummyTask;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests the abstract task: application context gating, the failure reporting trigger and
 * the mail delivery path, which moved from the removed MailMessage::send() to
 * MailerInterface::send() in TYPO3 v14.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
final class AbstractTaskTest extends FunctionalTestCase
{
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

        // Pin the sender so the generated reporting mail is deterministic.
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'typo3@example.org';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']    = 'TYPO3 Scheduler';
    }

    #[Test]
    public function executeRunsTheTaskWhenNoEnvironmentIsConfigured(): void
    {
        $task = new DummyTask();

        self::assertTrue($task->execute());
        self::assertSame(1, $task->executionCount);
    }

    #[Test]
    public function executeRunsTheTaskWhenTheCurrentContextIsListed(): void
    {
        $task              = new DummyTask();
        $task->environment = 'Production,' . Environment::getContext();

        self::assertTrue($task->execute());
        self::assertSame(1, $task->executionCount);
    }

    #[Test]
    public function executeSkipsTheTaskAndReportsSuccessWhenTheContextDoesNotMatch(): void
    {
        $task              = new DummyTask();
        $task->environment = 'Production';
        $task->taskResult  = false;

        // Flash messages are echoed on CLI by FlashMessageTrait.
        $this->expectOutputRegex('/Run skipped context mismatch/');

        self::assertTrue($task->execute());
        self::assertSame(0, $task->executionCount);
    }

    #[Test]
    public function executeReturnsFalseWhenTheTaskFailsAndReportingIsDisabled(): void
    {
        $task             = new DummyTask();
        $task->taskResult = false;

        self::assertFalse($task->execute());
        self::assertSame(1, $task->executionCount);
    }

    #[Test]
    public function executeRethrowsTheOriginalExceptionWhenReportingIsDisabled(): void
    {
        $task                   = new DummyTask();
        $task->throwOnExecution = new RuntimeException('task blew up', 1234);

        try {
            $task->execute();
            self::fail('Expected the original exception to be rethrown.');
        } catch (RuntimeException $exception) {
            self::assertSame('task blew up', $exception->getMessage());
            self::assertSame(1234, $exception->getCode());
        }
    }

    #[Test]
    public function executeSendsAReportingMailWhenTheTaskFailsAndReportingIsEnabled(): void
    {
        $sentSubjects = [];
        $sentBodies   = [];

        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock
            ->expects(self::once())
            ->method('send')
            ->willReturnCallback(
                static function (RawMessage $message) use (&$sentSubjects, &$sentBodies): void {
                    self::assertInstanceOf(Email::class, $message);

                    $sentSubjects[] = (string) $message->getSubject();
                    $sentBodies[]   = (string) $message->getTextBody();
                },
            );

        GeneralUtility::addInstance(MailerInterface::class, $mailerMock);

        $task                   = new DummyTask();
        $task->taskResult       = false;
        $task->enableReporting  = true;
        $task->reportingEmails  = 'ops@example.org';
        $task->reportingSubject = 'Nightly import failed';
        $task->setTaskUid(42);

        self::assertFalse($task->execute());

        self::assertSame(['Nightly import failed'], $sentSubjects);
        self::assertStringContainsString('The execution fails without specific error.', $sentBodies[0]);
        self::assertStringContainsString('Task ID: 42', $sentBodies[0]);
    }

    #[Test]
    public function executeWrapsMailTransportFailuresIntoAnExtensionException(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock
            ->expects(self::once())
            ->method('send')
            ->willThrowException(new TransportException('smtp is down'));

        GeneralUtility::addInstance(MailerInterface::class, $mailerMock);

        $task                   = new DummyTask();
        $task->throwOnExecution = new RuntimeException('task blew up');
        $task->enableReporting  = true;
        $task->reportingEmails  = 'ops@example.org';

        try {
            $task->execute();
            self::fail('Expected the transport failure to surface as an extension exception.');
        } catch (Exception $exception) {
            self::assertSame(
                'The reporting could not be sent due to the mail api throws the following error: smtp is down',
                $exception->getMessage(),
            );
            self::assertInstanceOf(TransportException::class, $exception->getPrevious());
        }
    }

    #[Test]
    public function executeReportsTheOriginalExceptionMessageBeforeRethrowing(): void
    {
        $body = null;

        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock
            ->expects(self::once())
            ->method('send')
            ->willReturnCallback(
                static function (RawMessage $message) use (&$body): void {
                    self::assertInstanceOf(Email::class, $message);

                    $body = (string) $message->getTextBody();
                },
            );

        GeneralUtility::addInstance(MailerInterface::class, $mailerMock);

        $task                   = new DummyTask();
        $task->throwOnExecution = new RuntimeException('database gone');
        $task->enableReporting  = true;
        $task->reportingEmails  = 'ops@example.org';

        try {
            $task->execute();
            self::fail('Expected the original exception to be rethrown.');
        } catch (RuntimeException $exception) {
            self::assertSame('database gone', $exception->getMessage());
        }

        self::assertStringContainsString('database gone', (string) $body);
    }

    /**
     * The reporting subject falls back to a generated one when none is configured.
     *
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function reportingSubjectDataProvider(): iterable
    {
        yield 'configured subject wins' => ['A custom subject', 'A custom subject'];
        yield 'empty subject falls back' => [
            '',
            'The execution of task 7(' . DummyTask::class . ') has failed!',
        ];
    }

    #[Test]
    #[DataProvider('reportingSubjectDataProvider')]
    public function reportingSubjectFallsBackToAGeneratedOne(string $configured, string $expected): void
    {
        $actual = null;

        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock
            ->expects(self::once())
            ->method('send')
            ->willReturnCallback(
                static function (RawMessage $message) use (&$actual): void {
                    self::assertInstanceOf(Email::class, $message);

                    $actual = (string) $message->getSubject();
                },
            );

        GeneralUtility::addInstance(MailerInterface::class, $mailerMock);

        $task                   = new DummyTask();
        $task->taskResult       = false;
        $task->enableReporting  = true;
        $task->reportingEmails  = 'ops@example.org';
        $task->reportingSubject = $configured;
        $task->setTaskUid(7);

        self::assertFalse($task->execute());
        self::assertSame($expected, $actual);
    }

    #[Test]
    public function reportingIsSentToEveryConfiguredRecipient(): void
    {
        $recipients = [];

        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock
            ->expects(self::once())
            ->method('send')
            ->willReturnCallback(
                static function (RawMessage $message) use (&$recipients): void {
                    self::assertInstanceOf(Email::class, $message);

                    foreach ($message->getTo() as $address) {
                        $recipients[] = $address->getAddress();
                    }
                },
            );

        GeneralUtility::addInstance(MailerInterface::class, $mailerMock);

        $task                  = new DummyTask();
        $task->taskResult      = false;
        $task->enableReporting = true;
        $task->reportingEmails = 'ops@example.org, dev@example.org';

        self::assertFalse($task->execute());
        self::assertSame(['ops@example.org', 'dev@example.org'], $recipients);
    }
}
