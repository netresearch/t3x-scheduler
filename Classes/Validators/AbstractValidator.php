<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Validators;

/**
 * Abstract validator.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
abstract class AbstractValidator
{
    /**
     * @var mixed
     */
    protected mixed $value;

    /**
     * @var string
     */
    protected string $fieldName;

    /**
     * @var string
     */
    protected string $message;

    /**
     * AbstractValidator constructor.
     *
     * @param string|null $value     Value to check
     * @param string      $fieldName Name of field
     * @param string      $message   Error message
     */
    public function __construct(?string $value, string $fieldName, string $message = '')
    {
        $this->value     = $value;
        $this->fieldName = $fieldName;
        $this->message   = $message;
    }

    /**
     * Validates the value and return the result of validation as bool.
     *
     * @return bool
     */
    abstract public function validate(): bool;

    /**
     * Returns the error message.
     *
     * @return string
     */
    abstract public function getErrorMessage(): string;
}
