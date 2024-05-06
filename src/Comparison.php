<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use DomainException;
use Dmcz\RangeDefiner\constants\Logic;
use Dmcz\RangeDefiner\constants\Comparator;
use Dmcz\RangeDefiner\constants\MatchPattern;

/**
 * Specifies the comparison type, value and logical relationships
 * 
 * @template T
 */
class Comparison
{   
    /**
     * Constructs a new comparison condition with specified parameters.
     * 
     * @param Comparator $comparator The type of comparison to apply (e.g., EQ, LT).
     * @param mixed $value           The value or values involved in the comparison, determined by the comparator:
     *                               - EQ, NEQ, LT, GT, LTE, GTE: Single value. Retrieve using `$this->getValue()`.
     *                               - NULL/NOTNULL: Null (represents absence of a value).
     *                               - IN/NOTIN: Array of permissible or excluded values. Retrieve using `$this->getValue()`.
     *                               - BETWEEN/INCLUSIVE: Array with two elements [minValue, maxValue]. Retrieve the minimum using `$this->getMin()` and the maximum using `$this->getMax()`.
     *                               - MATCH: Array where the first element is the value and the second is the match pattern. Retrieve the value using `$this->getValue()` and the pattern using `$this->getMatchPattern()`.
     * @param Logic $logic          The logical relationship this condition contributes to (e.g., AND, OR).
     */
    public function __construct(
        public readonly Comparator $comparator,
        public readonly mixed $value,
        public readonly Logic $logic
    ){}


    /**
     * Retrieves the appropriate value for the comparison based on the comparator type.
     * For 'MATCH' comparator, it returns the value to be matched against the pattern.
     * Otherwise, it returns the whole value array or a single value.
     *
     * @return mixed The value used for the comparison or the first element of the value array when using MATCH.
     */
    public function getValue()
    {
        return match ($this->comparator) {
            Comparator::MATCH => $this->value[0],
            default => $this->value,
        };
    }

    /**
     * Retrieves the minimum value from the value array if the comparator is BETWEEN or INCLUSIVE.
     * Throws an exception for any other type of comparator as they do not use a minimum value.
     *
     * @return mixed The minimum value.
     * @throws DomainException  If the comparator does not support a minimum value.
     */
    public function getMin()
    {
        return match ($this->comparator) {
            Comparator::BETWEEN, Comparator::INCLUSIVE => $this->value[0],
            default => throw new DomainException('the comparator "' . $this->comparator . '" do not have min value')
        };
    }

    /**
     * Retrieves the maximum value from the value array if the comparator is BETWEEN or INCLUSIVE.
     * Throws an exception for any other type of comparator as they do not use a maximum value.
     *
     * @return mixed The maximum value.
     * @throws DomainException  If the comparator does not support a maximum value.
     */
    public function getMax()
    {
        return match ($this->comparator) {
            Comparator::BETWEEN, Comparator::INCLUSIVE => $this->value[1],
            default => throw new DomainException('the comparator "' . $this->comparator . '" do not have min value')
        };
    }

    /**
     * Retrieves the match pattern from the value array if the comparator is MATCH.
     * Throws an exception for any other type of comparator as they do not use a match pattern.
     *
     * @return mixed The match pattern.
     * @throws DomainException  If the comparator does not support a match pattern.
     */
    public function getMatchPattern()
    {
        return match ($this->comparator) {
            Comparator::MATCH => $this->value[1],
            default => throw new DomainException('the comparator "' . $this->comparator . '" do not have match pattern')
        };
    }

    /**
     * Creates a new instance for equality comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function equal($value, Logic $logic): static
    {
        return new static(Comparator::EQ, $value, $logic);
    }

    /**
     * Creates a new instance for non-equality comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function notEqual($value, Logic $logic): static
    {
        return new static(Comparator::NEQ, $value, $logic);
    }

    /**
     * Creates a new instance for less-than comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function lessThan($value, Logic $logic): static
    {
        return new static(Comparator::LT, $value, $logic);
    }

    /**
     * Creates a new instance for less-than-or-equal comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function lessEqual($value, Logic $logic): static
    {
        return new static(Comparator::LTE, $value, $logic);
    }

    /**
     * Creates a new instance for greater-than comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function greaterThan($value, Logic $logic): static
    {
        return new static(Comparator::GT, $value, $logic);
    }

    /**
     * Creates a new instance for greater-than-or-equal comparison.
     *
     * @param T $value
     * @param Logic $logic
     * @return static
     */
    public static function greaterEqual($value, Logic $logic): static
    {
        return new static(Comparator::GTE, $value, $logic);
    }

    /**
     * Creates a new instance for 'in' comparison.
     *
     * @param T[] $value
     * @param Logic $logic
     * @return static
     */
    public static function withIn(array $value, Logic $logic): static
    {
        return new static(Comparator::IN, $value, $logic);
    }

    /**
     * Creates a new instance for 'not-in' comparison.
     *
     * @param T[] $value
     * @param Logic $logic
     * @return static
     */
    public static function notIn(array $value, Logic $logic): static
    {
        return new static(Comparator::NOTIN, $value, $logic);
    }

    /**
     * Creates a new instance for 'null' comparison.
     *
     * @param Logic $logic
     * @return static
     */
    public static function isNull(Logic $logic): static
    {
        return new static(Comparator::NULL, null, $logic);
    }

    /**
     * Creates a new instance for 'not-null' comparison.
     *
     * @param Logic $logic
     * @return static
     */
    public static function notNull(Logic $logic): static
    {
        return new static(Comparator::NOTNULL, null, $logic);
    }

    /**
     * Creates a new instance for between comparison.
     *
     * @param T $min
     * @param T $max
     * @param Logic $logic
     * @return static
     */
    public static function between($min, $max, Logic $logic): static
    {
        return new static(Comparator::BETWEEN, [$min, $max], $logic);
    }

    /**
     * Creates a new instance for inclusive comparison.
     *
     * @param T $min
     * @param T $max
     * @param Logic $logic
     * @return static
     */
    public static function inclusive($min, $max, Logic $logic): static
    {
        return new static(Comparator::INCLUSIVE, [$min, $max], $logic);
    }

    /**
     * Creates a new instance for match comparison.
     *
     * @param T $value
     * @param MatchPattern $matchPattern
     * @param Logic $logic
     * @return static
     */
    public static function match($value, MatchPattern $matchPattern, Logic $logic): static
    {
        return new static(Comparator::MATCH, [$value, $matchPattern], $logic);
    }
}
