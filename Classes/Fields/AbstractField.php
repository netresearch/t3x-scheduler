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
     * @var string
     */
    protected string $description = '';

    /**
     * @var bool|int|float|string|object|int[]|string[]|null
     */
    protected array|bool|int|float|string|object|null $value = null;

    /**
     * AbstractField constructor.
     *
     * @param string                                    $identifier The field identifier
     * @param string                                    $label      The label of the field
     * @param bool|int|float|string|int[]|string[]|null $value      The value of the field
     */
    public function __construct(string $identifier, string $label, array|bool|int|float|string|null $value = null)
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
     * @return bool|int|float|string|object|int[]|string[]|null
     */
    public function getValue(): array|bool|int|float|string|object|null
    {
        return $this->value;
    }

    /**
     * @param bool|int|float|string|object|int[]|string[]|null $value
     *
     * @return AbstractField
     */
    public function setValue(float|array|bool|int|string|object|null $value): AbstractField
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return AbstractField
     */
    public function setDescription(string $description): AbstractField
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the array for the additional field.
     *
     * @return array<string, string>
     */
    public function getAdditionalField(): array
    {
        return [
            'code'        => $this->getFieldHtml(),
            'type'        => $this->getType(),
            'label'       => $this->getLabel(),
            'description' => $this->getDescription(),
        ];
    }
}
