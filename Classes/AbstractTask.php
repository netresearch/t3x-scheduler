<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler;

use Netresearch\NrScheduler\Traits\FlashMessageTrait;
use Netresearch\NrScheduler\Traits\TranslationTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

use function in_array;

/**
 * Abstract scheduler task.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
abstract class AbstractTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    use FlashMessageTrait;
    use TranslationTrait;

    /**
     * @var bool
     */
    public bool $enableReporting = false;

    /**
     * @var string
     */
    public string $reportingEmails = '';

    /**
     * @var string
     */
    public string $reportingSubject = '';

    /**
     * @var string
     */
    public string $reportingMessage = '';

    /**
     * @var string
     */
    public string $environment = '';

    /**
     * Executes the task.
     *
     * @return bool
     */
    abstract public function executeTask(): bool;

    /**
     * Executes the task and send reporting if the execution fails and the reporting is enabled.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function execute(): bool
    {
        if (!$this->isRunnableInContext()) {
            $this->addInfoMessage('Run skipped context mismatch');

            return true;
        }

        try {
            $result = $this->executeTask();

            if ($result === false) {
                $this->sendReporting('The execution fails without specific error.');
            }
        } catch (\Exception $exception) {
            $this->sendReporting($exception->getMessage());

            throw $exception;
        }

        return $result;
    }

    /**
     * Send the reporting.
     *
     * @param string $message Message to send
     *
     * @throws Exception
     */
    private function sendReporting(string $message): void
    {
        if (!$this->enableReporting) {
            return;
        }

        try {
            $sentMails = $this->sendEmail(
                $this->getReportingEmailsAsArray(),
                $this->getReportingContent($message)
            );

            if ($sentMails === false) {
                throw new Exception(
                    'The reporting could not be sent!'
                );
            }
        } catch (\Exception $exception) {
            throw new Exception(
                'The reporting could not be sent due to the mail api throws the following error: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Send the reporting emails.
     *
     * @param string[] $recipients Array with email addresses the mail should send to
     * @param string   $email      The email content which will be sent
     *
     * @return bool
     */
    private function sendEmail(array $recipients, string $email): bool
    {
        /** @var MailMessage $mailApi */
        $mailApi = GeneralUtility::makeInstance(MailMessage::class);

        $mailApi
            ->setFrom(MailUtility::getSystemFrom())
            ->setTo($recipients)
            ->setSubject($this->getReportingEmailSubject())
            ->text($email);

        return $mailApi->send();
    }

    /**
     * Returns the email-addresses where the reporting should be sent to.
     *
     * @return string[]
     */
    private function getReportingEmailsAsArray(): array
    {
        if ($this->reportingEmails === '') {
            return [];
        }

        return GeneralUtility::trimExplode(',', $this->reportingEmails);
    }

    /**
     * Returns the subject of the reporting email.
     *
     * @return string
     */
    private function getReportingEmailSubject(): string
    {
        if ($this->reportingSubject === '') {
            return 'The execution of task ' . $this->taskUid . '(' . static::class . ') has failed!';
        }

        return $this->reportingSubject;
    }

    /**
     * Returns the email content to send.
     *
     * @param string $message Message
     *
     * @return string
     */
    private function getReportingContent(string $message): string
    {
        $newLine = "\r\n";
        $content = $message . $newLine . $newLine;

        if ($this->reportingMessage !== '') {
            $content .= $this->reportingMessage;
            $content .= $newLine . $newLine;
        }

        $content .= '----------------' . $newLine;
        $content .= 'Task info' . $newLine;
        $content .= '----------------' . $newLine;
        $content .= 'Task ID: ' . $this->getTaskUid() . $newLine;
        $content .= 'Task execuiton: ' . date('Y-m-d H:i:s') . $newLine;

        return $content . ('Task error: ' . $message . $newLine);
    }

    /**
     * Returns true if the task should run in the current application context.
     *
     * @return bool
     */
    private function isRunnableInContext(): bool
    {
        if ($this->environment === '') {
            return true;
        }

        $currentContext = (string) Environment::getContext();
        $contexts       = GeneralUtility::trimExplode(',', $this->environment);

        return in_array($currentContext, $contexts, true);
    }
}
