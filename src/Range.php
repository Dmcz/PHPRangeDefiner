<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use Dmcz\RangeDefiner\Constants\Logic;
use Dmcz\RangeDefiner\Constants\MatchPattern;

/**
 * Manages a series of constraints that define complex conditional logic.
 *
 * @template T
 */
class Range
{
    /**
     * A collection of constraints, each defining a set of comparisons.
     *
     * @var Constraint<T>[]
     */
    protected array $constraints;

    /**
     * Initializes a new instance of the Range class, starting with a default empty Constraint.
     */
    public function __construct(
        public readonly string $name = '',
        public readonly Logic $logic = Logic::AND,
    ) {
        $this->constraints = [new Constraint()];
    }

    /**
     * get constraints.
     *
     * @return Constraint<T>[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Adds a new constraint to the collection.
     *
     * @param Constraint<T> $constraint
     */
    public function push(Constraint $constraint): self
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * Applies a new constraint with a specified logical operation to the collection.
     */
    public function applyNew(Logic $logic = Logic::AND): self
    {
        return $this->push(new Constraint($logic));
    }

    /**
     * Adds a comparison to the last constraint in the list.
     *
     * @param Comparison<T> $comparison
     */
    public function append(Comparison $comparison): self
    {
        $this->constraints[count($this->constraints) - 1]->push($comparison);
        return $this;
    }

    /**
     * Adds an equality comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared for equality
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function equal($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::equal($value, $logic));
        return $this;
    }

    /**
     * Adds a non-equality comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared for non-equality
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function notEqual($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::notEqual($value, $logic));
        return $this;
    }

    /**
     * Adds a greater-than comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared as greater than the reference
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function greaterThan($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::greaterThan($value, $logic));
        return $this;
    }

    /**
     * Adds a greater-than-or-equal comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared as greater than or equal to the reference
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function greaterEqual($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::greaterEqual($value, $logic));
        return $this;
    }

    /**
     * Adds a less-than comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared as less than the reference
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function lessThan($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::lessThan($value, $logic));
        return $this;
    }

    /**
     * Adds a less-than-or-equal comparison to the current chain of constraints.
     *
     * @param T $value the value to be compared as less than or equal to the reference
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function lessEqual($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::lessEqual($value, $logic));
        return $this;
    }

    /**
     * Adds included in a specified set comparison to the current chain of constraints.
     *
     * @param T[] $value array of values defining the range from which the values should be included
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function withIn($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::withIn($value, $logic));
        return $this;
    }

    /**
     * Adds excluded in a specified set comparison to the current chain of constraints.
     *
     * @param T[] $value array of values defining the range from which the values should be excluded
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function notIn($value, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::notIn($value, $logic));
        return $this;
    }

    /**
     * Adds a null comparison to the current chain of constraints.
     * Asserts that the field is null. Uses logical AND by default.
     *
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function isNull(Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::isNull($logic));
        return $this;
    }

    /**
     * Adds a not-null comparison to the current chain of constraints.
     * Asserts that the field is not null. Uses logical AND by default.
     *
     * @param Logic $logic the logical operator to apply (default: AND)
     */
    public function notNull(Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::notNull($logic));
        return $this;
    }

    /**
     * Adds a matching comparison to the last constraint based on a specified pattern.
     *
     * @param T $value the value to check against the pattern
     * @param MatchPattern $pattern the pattern to match against the value
     * @param Logic $logic the logic for this comparison
     */
    public function match($value, MatchPattern $pattern, Logic $logic = Logic::AND): self
    {
        $this->append(Comparison::match($value, $pattern, $logic));
        return $this;
    }
}
