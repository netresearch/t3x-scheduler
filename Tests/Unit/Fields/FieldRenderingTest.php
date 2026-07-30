<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Tests\Unit\Fields;

use Netresearch\NrScheduler\Fields\CheckBoxField;
use Netresearch\NrScheduler\Fields\MultiSelectField;
use Netresearch\NrScheduler\Fields\PasswordField;
use Netresearch\NrScheduler\Fields\SelectField;
use Netresearch\NrScheduler\Fields\TextAreaField;
use Netresearch\NrScheduler\Fields\TextField;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Tests the field builders. These render through TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder,
 * which TYPO3 v14 ships as Fluid 5, so the produced markup is what pins that dependency.
 *
 * @author  Netresearch DTT GmbH <info@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
final class FieldRenderingTest extends UnitTestCase
{
    #[Test]
    public function textFieldRendersAnInputCarryingItsValue(): void
    {
        $html = (new TextField('reportingEmails', 'Reporting emails', 'ops@example.org'))->getFieldHtml();

        self::assertStringContainsString('id="reportingEmails"', $html);
        self::assertStringContainsString('name="tx_scheduler[reportingEmails]"', $html);
        self::assertStringContainsString('value="ops@example.org"', $html);
    }

    #[Test]
    public function passwordFieldRendersAPasswordInput(): void
    {
        $html = (new PasswordField('secret', 'Secret', 'hunter2'))->getFieldHtml();

        self::assertStringContainsString('type="password"', $html);
        self::assertStringContainsString('name="tx_scheduler[secret]"', $html);
    }

    #[Test]
    public function textAreaFieldRendersAClosedTagWithItsContent(): void
    {
        $html = (new TextAreaField('reportingMessage', 'Reporting message', 'Check the log.'))->getFieldHtml();

        self::assertStringContainsString('<textarea', $html);
        self::assertStringContainsString('Check the log.', $html);
        self::assertStringContainsString('</textarea>', $html);
    }

    #[Test]
    public function checkBoxFieldAlwaysEmitsAnUncheckedFallbackAndMarksCheckedState(): void
    {
        $unchecked = (new CheckBoxField('enableReporting', 'Enable reporting', false))->getFieldHtml();

        self::assertStringContainsString('<input type="hidden" name="tx_scheduler[enableReporting]" value="0">', $unchecked);
        self::assertStringNotContainsString('checked="checked"', $unchecked);

        $checked = (new CheckBoxField('enableReporting', 'Enable reporting', true))->getFieldHtml();

        self::assertStringContainsString('checked="checked"', $checked);
    }

    #[Test]
    public function selectFieldMarksTheSelectedOption(): void
    {
        $field = new SelectField('environment', 'Environment', 'Production');
        $field->setOptions([
            'Development' => 'Development',
            'Production'  => 'Production',
        ]);

        $html = $field->getFieldHtml();

        self::assertStringContainsString('<select', $html);
        self::assertStringContainsString('name="tx_scheduler[environment]"', $html);
        self::assertStringContainsString('<option title="Production" value="Production" selected="selected">', $html);
        self::assertStringNotContainsString('value="Development" selected', $html);
    }

    #[Test]
    public function multiSelectFieldUsesAnArrayFieldNameAndMarksEverySelectedOption(): void
    {
        $field = new MultiSelectField('environment', 'Environment', ['Development', 'Production']);
        $field->setOptions([
            'Development' => 'Development',
            'Staging'     => 'Staging',
            'Production'  => 'Production',
        ]);

        $html = $field->getFieldHtml();

        self::assertStringContainsString('name="tx_scheduler[environment][]"', $html);
        self::assertStringContainsString('multiple="multiple"', $html);
        self::assertSame(2, substr_count($html, 'selected="selected"'));
        self::assertStringNotContainsString('value="Staging" selected', $html);
    }
}
