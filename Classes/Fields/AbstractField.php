<?php

/**
 * This file is part of the package netresearch/nr-scheduler.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Netresearch\NrScheduler\Fields;

/**
 * Abstract field.
 *
 * @author  Axel Seemann <axel.seemann@netresearch.de>
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license Netresearch https://www.netresearch.de
 * @link    https://www.netresearch.de
 */
abstract class AbstractField
{
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $identifier;

    /**
     * @var string
     */
    protected string $label;

    /**
     * @var bool|int|string|null
     */
    protected bool|int|float|string|null $value;

    /**
     * AbstractField constructor.
     *
     * @param string                     $identifier The field identifier
     * @param string                     $label      The label of the field
     * @param bool|int|float|string|null $value      The value of the field
     */
    public function __construct(string $identifier, string $label, bool|int|float|string|null $value)
    {
        $this->identifier = $identifier;
        $this->label      = $label;
        $this->value      = $value;
    }

    /**
     * Returns the field HTML.
     *
     * @return string
     */
    abstract public function getFieldHtml(): string;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Returns the field name.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return 'tx_scheduler[' . $this->getIdentifier() . ']';
    }

    /**
     * Returns the label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns the value.
     *
     * @return bool|int|float|string|null
     */
    public function getValue(): bool|int|float|string|null
    {
        return $this->value;
    }

    /**
     * Returns the array for the additional field.
     *
     * @return array<string, string>
     */
    public function getAdditionalField(): array
    {
        return [
            'code'  => $this->getFieldHtml(),
            'type'  => $this->getType(),
            'label' => $this->getLabel(),
        ];
    }
}
