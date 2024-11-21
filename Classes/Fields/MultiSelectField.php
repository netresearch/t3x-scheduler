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

use function in_array;
use function is_array;

/**
 * MultiSelect field.
 *
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
class MultiSelectField extends SelectField
{
    /**
     * Returns the field name.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return 'tx_scheduler[' . $this->getIdentifier() . '][]';
    }

    /**
     * Returns the select box options HTML.
     *
     * @return string
     */
    protected function getSelectOptionsHtml(): string
    {
        $optionsHtml = '';

        foreach ($this->options as $value => $label) {
            $optionTag = new TagBuilder();
            $optionTag->setTagName('option');
            $optionTag->forceClosingTag(true);
            $optionTag->addAttribute('title', $label);
            $optionTag->addAttribute('value', $value);
            $optionTag->setContent($label);

            $selectedValue = $this->getValue();

            if (is_array($selectedValue)) {
                if (in_array($value, $this->getValue(), true)) {
                    $optionTag->addAttribute('selected', 'selected');
                }
            } elseif ($value === $this->getValue()) {
                $optionTag->addAttribute('selected', 'selected');
            }

            $optionsHtml .= $optionTag->render();
        }

        return $optionsHtml;
    }

    /**
     * Returns the field HTML.
     *
     * @return string
     */
    public function getFieldHtml(): string
    {
        $tagBuilder = $this->getSelectTagBuilder();
        $tagBuilder->addAttribute('multiple', 'multiple');
        $tagBuilder->setContent($this->getSelectOptionsHtml());

        return $tagBuilder->render();
    }
}
