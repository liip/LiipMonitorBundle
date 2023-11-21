<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Utility;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 */
final class Percent implements \Stringable
{
    private function __construct(private float $value)
    {
    }

    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * @param float $value Percentage in "decimal form" (ie 0.051 for 5.1%)
     */
    public static function fromDecimal(float $value): self
    {
        return new self($value);
    }

    /**
     * @param float|string $percentage In "percentage form" (ie 5.1 for 5.1%)
     */
    public static function from(float|int|string $percentage): self
    {
        if (!\is_numeric($percentage) && \preg_match('#^(-?[\d,]+(.[\d,]+)?)([\s\-_]+)?%$#', \trim($percentage), $matches)) {
            return new self((float) $matches[1] / 100);
        }

        if (!\is_numeric($percentage)) {
            throw new \InvalidArgumentException(\sprintf('Value "%s" is not numeric', $percentage));
        }

        return new self($percentage / 100);
    }

    /**
     * @throws \DivisionByZeroError if $total is 0 and $divisionByZeroValue is not provided
     */
    public static function calculate(float $value, float $total, ?float $divisionByZeroValue = null): self
    {
        try {
            return new self($value / $total);
        } catch (\DivisionByZeroError $e) {
            if (null !== $divisionByZeroValue) {
                return new self($divisionByZeroValue);
            }

            throw $e;
        }
    }

    /**
     * @return float Percentage in "decimal form" (ie 0.051 for 5.1%)
     */
    public function decimal(): float
    {
        return $this->value;
    }

    /**
     * @return float Percentage in "percentage form" (ie 5.1 for 5.1%)
     */
    public function percentage(): float
    {
        return $this->value * 100;
    }

    public function format(int $precision = 0): string
    {
        return \number_format($this->percentage(), $precision).'%';
    }

    public function constrain(float $min = 0.0, float $max = 1.0): self
    {
        return new self(\min($max, \max($min, $this->value)));
    }

    public function constrainLower(float $min = 0.0): self
    {
        return new self(\max($min, $this->value));
    }

    public function constrainUpper(float $max = 1.0): self
    {
        return new self(\min($max, $this->value));
    }

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->value >= $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isLessThanOrEqualTo(self $other): bool
    {
        return $this->value <= $other->value;
    }
}
