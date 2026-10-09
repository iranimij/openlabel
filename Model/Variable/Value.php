<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable;

/**
 * A typed variable value. The Renderer formats it for the locale; empty values let the label hide itself.
 */
// phpcs:disable Magento2.Functions.StaticFunction -- named constructors of a value object, never intercepted.
class Value
{
    public const TEXT = 'text';
    public const HTML = 'html';
    public const NUMBER = 'number';
    public const CURRENCY = 'currency';
    public const DATE = 'date';

    /**
     * @param mixed $raw
     * @param string $type one of the type constants
     * @param int $precision decimals for NUMBER
     */
    public function __construct(
        public readonly mixed $raw,
        public readonly string $type,
        public readonly int $precision = 0
    ) {
    }

    /**
     * @param string|null $text escaped by the Renderer
     * @return self
     */
    public static function text(?string $text): self
    {
        return new self($text, self::TEXT);
    }

    /**
     * @param string $html trusted markup produced by code, never by merchants or shoppers
     * @return self
     */
    public static function html(string $html): self
    {
        return new self($html, self::HTML);
    }

    /**
     * @param int|float|null $number
     * @param int $precision
     * @return self
     */
    public static function number(int|float|null $number, int $precision = 0): self
    {
        return new self($number, self::NUMBER, $precision);
    }

    /**
     * @param float|null $amount in the store's display currency
     * @return self
     */
    public static function currency(?float $amount): self
    {
        return new self($amount, self::CURRENCY, 2);
    }

    /**
     * @param string|null $date Y-m-d H:i:s
     * @return self
     */
    public static function date(?string $date): self
    {
        return new self($date, self::DATE);
    }

    /**
     * @return self
     */
    public static function empty(): self
    {
        return new self(null, self::TEXT);
    }

    /**
     * Null, empty string or zero: the "hide label when a variable is zero/empty" trigger.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        if ($this->raw === null || $this->raw === '') {
            return true;
        }
        if ($this->type === self::NUMBER || $this->type === self::CURRENCY) {
            return abs((float) $this->raw) < 0.000001;
        }

        return false;
    }
}
