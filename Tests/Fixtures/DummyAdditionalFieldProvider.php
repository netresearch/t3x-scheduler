<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Fixtures;

use Netresearch\NrScheduler\AbstractAdditionalFieldProvider;
use Netresearch\NrScheduler\Fields\SelectField;
use Netresearch\NrScheduler\Fields\TextField;

/**
 * Concrete additional field provider used to exercise the abstract base provider.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 *
 * @deprecated since 2.0.0, shares the lifetime of AbstractAdditionalFieldProvider, which
 *             TYPO3 removes in v15.0. Kept only to test that class while it is supported.
 */
final class DummyAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Validators applied to the "demoText" field.
     *
     * @var class-string[]
     */
    public static array $demoTextValidators = [];

    /**
     * Returns the field configuration.
     *
     * @return array<string, array<string, bool|int|string|string[]>>
     */
    public function getFieldConfiguration(): array
    {
        return [
            'demoText' => [
                'default'         => 'the-default',
                'type'            => TextField::class,
                'validators'      => self::$demoTextValidators,
                'translationFile' => '',
            ],
            'demoSelect' => [
                'default'         => 'a',
                'type'            => SelectField::class,
                'options'         => ['a' => 'Option A', 'b' => 'Option B'],
                'validators'      => [],
                'translationFile' => '',
            ],
        ];
    }
}
