<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Fields;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Select field.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 *
 * @see    https://www.netresearch.de
 */
class SelectField extends AbstractField
{
    protected string $type = 'select';

    /**
     * @var array<array-key, string>
     */
    protected array $options = [];

    /**
     * @param array<string, string> $options
     *
     * @return SelectField
     */
    public function setOptions(array $options): SelectField
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Returns the select box tag builder instance.
     *
     * @return TagBuilder
     */
    protected function getSelectTagBuilder(): TagBuilder
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setTagName('select');
        $tagBuilder->forceClosingTag(true);
        $tagBuilder->addAttribute('id', $this->getIdentifier());
        $tagBuilder->addAttribute('name', $this->getFieldName());
        $tagBuilder->addAttribute('class', 'form-select');

        return $tagBuilder;
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

            if ($value === $this->getValue()) {
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
        $tagBuilder->setContent($this->getSelectOptionsHtml());

        return $tagBuilder->render();
    }
}
