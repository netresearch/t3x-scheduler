<?php

/*
 * This file is part of the package netresearch/nr-scheduler.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 * SPDX-FileCopyrightText: Netresearch DTT GmbH
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Fields;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Textarea field.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license GPL-3.0-or-later
 *
 * @see    https://www.netresearch.de
 */
class TextAreaField extends AbstractField
{
    protected string $type = 'textarea';

    /**
     * Returns the field HTML.
     *
     * @return string
     */
    public function getFieldHtml(): string
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->forceClosingTag(true);
        $tagBuilder->setTagName('textarea');
        $tagBuilder->addAttribute('id', $this->getIdentifier());
        $tagBuilder->addAttribute('name', $this->getFieldName());
        $tagBuilder->addAttribute('class', 'form-control');
        // TagBuilder escapes attribute values but not the tag content.
        $value = $this->getValue();
        $tagBuilder->setContent(is_string($value) ? htmlspecialchars($value) : $value);

        return $tagBuilder->render();
    }
}
