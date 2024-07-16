<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use Dmcz\RangeDefiner\Constants\Comparator;
use Dmcz\RangeDefiner\Constants\Logic;
use Dmcz\RangeDefiner\Constants\MatchPattern;
use DomainException;

/**
 * Specifies the comparison type, value and logical relationships.
 *
 * @template T
 */
class Comparison
{
    /**
     * Constructs a new comparison condition with specified parameters.
     *
     * @param Comparator $comparator The type of comparison to apply (e.g., EQ, LT).
     * @param mixed $value The value or values involved in the comparison, determined by the comparator:
     *                     - EQ, NEQ, LT, GT, LTE, GTE: Single value. Retrieve using `$this->getValue()`.
     *                     - NULL/NOTNULL: Null (represents absence of a value).
     *                     - IN/NOTIN: Array of permissible or excluded values. Retrieve using `$this->getValue()`.
     *                     - MATCH: Array where the first element is the value and the second is the match pattern. Retrieve the value using `$this->getValue()` and the pattern using `$this->getMatchPattern()`.
     * @param Logic $logic The logical relationship this condition contributes to (e.g., AND, OR).
     */
    public function __construct(
        public readonly Comparator $comparator,
        public readonly mixed $value,
        public readonly Logic $logic
    ) {
    }

    /**
     * Retrieves the appropriate value for the comparison based on the comparator type.
     * For 'MATCH' comparator, it returns the value to be matched against the pattern.
     * Otherwise, it returns the whole value array or a single value.
     *
     * @return mixed the value used for the comparison or the first element of the value array when using MATCH
     */
    public function getValue()
    {
        return match ($this->comparator) {
            Comparator::MATCH => $this->value[0],
            default => $this->value,
        };
    }

    /**
     * Retrieves the match pattern from the value array if the comparator is MATCH.
     * Throws an exception for any other type of comparator as they do not use a match pattern.
     *
     * @return mixed the match pattern
     * @throws DomainException if the comparator does not support a match pattern
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
     */
    public static function equal($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::EQ, $value, $logic);
    }

    /**
     * Creates a new instance for non-equality comparison.
     *
     * @param T $value
     */
    public static function notEqual($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::NEQ, $value, $logic);
    }

    /**
     * Creates a new instance for less-than comparison.
     *
     * @param T $value
     */
    public static function lessThan($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::LT, $value, $logic);
    }

    /**
     * Creates a new instance for less-than-or-equal comparison.
     *
     * @param T $value
     */
    public static function lessEqual($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::LTE, $value, $logic);
    }

    /**
     * Creates a new instance for greater-than comparison.
     *
     * @param T $value
     */
    public static function greaterThan($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::GT, $value, $logic);
    }

    /**
     * Creates a new instance for greater-than-or-equal comparison.
     *
     * @param T $value
     */
    public static function greaterEqual($value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::GTE, $value, $logic);
    }

    /**
     * Creates a new instance for 'in' comparison.
     *
     * @param T[] $value
     */
    public static function withIn(array $value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::IN, $value, $logic);
    }

    /**
     * Creates a new instance for 'not-in' comparison.
     *
     * @param T[] $value
     */
    public static function notIn(array $value, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::NOTIN, $value, $logic);
    }

    /**
     * Creates a new instance for 'null' comparison.
     */
    public static function isNull(Logic $logic = Logic::AND): static
    {
        return new static(Comparator::NULL, null, $logic);
    }

    /**
     * Creates a new instance for 'not-null' comparison.
     */
    public static function notNull(Logic $logic = Logic::AND): static
    {
        return new static(Comparator::NOTNULL, null, $logic);
    }

    /**
     * Creates a new instance for match comparison.
     *
     * @param T $value
     */
    public static function match($value, MatchPattern $matchPattern, Logic $logic = Logic::AND): static
    {
        return new static(Comparator::MATCH, [$value, $matchPattern], $logic);
    }
}
