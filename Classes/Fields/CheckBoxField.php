<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Fields;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Checkbox field.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
class CheckBoxField extends AbstractField
{
    protected string $type = 'checkToggle';

    /**
     * Returns the field HTML.
     *
     * @return string
     */
    public function getFieldHtml(): string
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setTagName('input');
        $tagBuilder->addAttribute('id', $this->getIdentifier());
        $tagBuilder->addAttribute('name', $this->getFieldName());
        $tagBuilder->addAttribute('type', 'checkbox');
        $tagBuilder->addAttribute('class', 'form-check-input');
        $tagBuilder->addAttribute('value', '1');

        if ($this->getValue()) {
            $tagBuilder->addAttribute('checked', 'checked');
        }

        return <<<HTML
<input type="hidden" name="{$this->getFieldName()}" value="0">
{$tagBuilder->render()}
HTML;
    }
}
