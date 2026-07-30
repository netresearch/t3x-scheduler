<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Fixtures;

use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\SchedulerManagementAction;

/**
 * Puts a scheduler module controller into a given management action.
 *
 * TYPO3 v14 added SchedulerModuleController::setCurrentAction(); TYPO3 v13.4 only has the
 * private $currentAction property the controller assigns while dispatching. Writing the
 * property directly therefore works on both supported core versions.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
trait SchedulerManagementActionTrait
{
    /**
     * Assigns the given action to the scheduler module controller.
     *
     * @param SchedulerModuleController $schedulerModule Controller to modify
     * @param SchedulerManagementAction $action          Action the controller should report
     *
     * @return void
     *
     * @throws ReflectionException
     */
    private function setSchedulerManagementAction(
        SchedulerModuleController $schedulerModule,
        SchedulerManagementAction $action,
    ): void {
        $property = (new ReflectionClass(SchedulerModuleController::class))
            ->getProperty('currentAction');

        $property->setValue($schedulerModule, $action);
    }
}
