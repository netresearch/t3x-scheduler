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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Boots a real TYPO3 instance with the extension installed. This is the check the
 * TYPO3 v12 code could not survive: TYPO3\CMS\Scheduler\Task\Enumeration\Action was
 * removed, so the additional field provider used to fail at autoload time.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 *
 * @deprecated since 2.0.0, shares the lifetime of AbstractAdditionalFieldProvider, which
 *             TYPO3 removes in v15.0. Kept only to test that class while it is supported.
 */
final class ExtensionIntegrationTest extends FunctionalTestCase
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

    #[Test]
    public function extensionIsLoadedInTheTestInstance(): void
    {
        self::assertTrue(ExtensionManagementUtility::isLoaded('nr_scheduler'));
        self::assertTrue(ExtensionManagementUtility::isLoaded('scheduler'));
    }

    #[Test]
    public function additionalFieldProviderResolvesAgainstTheInstalledCore(): void
    {
        $subject = GeneralUtility::makeInstance(DummyAdditionalFieldProvider::class);

        self::assertInstanceOf(AbstractAdditionalFieldProvider::class, $subject);
        self::assertContains(
            AdditionalFieldProviderInterface::class,
            class_implements($subject),
        );
    }

    #[Test]
    public function taskResolvesAgainstTheInstalledCore(): void
    {
        $task = GeneralUtility::makeInstance(DummyTask::class);

        self::assertInstanceOf(AbstractTask::class, $task);
        self::assertSame(0, $task->getTaskUid());
    }
}
