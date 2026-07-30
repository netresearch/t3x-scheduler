<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Fixtures;

use Exception;
use Netresearch\NrScheduler\AbstractTask;

/**
 * Concrete task used to exercise the reporting and context gating behaviour of
 * the abstract base task.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
final class DummyTask extends AbstractTask
{
    /**
     * Number of times executeTask() was invoked.
     */
    public int $executionCount = 0;

    /**
     * Value executeTask() returns.
     */
    public bool $taskResult = true;

    /**
     * Exception executeTask() throws instead of returning a result.
     */
    public ?Exception $throwOnExecution = null;

    /**
     * Executes the task.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function executeTask(): bool
    {
        ++$this->executionCount;

        if ($this->throwOnExecution instanceof Exception) {
            throw $this->throwOnExecution;
        }

        return $this->taskResult;
    }
}
