<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Fixtures;

use Netresearch\NrScheduler\Validators\AbstractValidator;

/**
 * Validator that always rejects, used to exercise the validation error path.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
final class RejectingValidator extends AbstractValidator
{
    /**
     * Validates the value and return the result of validation as bool.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return false;
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Field "' . $this->fieldName . '" rejected value "' . $this->value . '".';
    }
}
