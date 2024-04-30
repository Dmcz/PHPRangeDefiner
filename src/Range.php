<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use Dmcz\RangeDefiner\Constraint;
use Dmcz\RangeDefiner\constants\Logic;
use Dmcz\RangeDefiner\constants\Comparator;
use Dmcz\RangeDefiner\constants\MatchPattern;

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
    public function __construct()
    {
        $this->constraints = [new Constraint];
    }

    /**
     * get constraints
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
     * @return self
     */
    public function push(Constraint $constraint): self
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * Applies a new constraint with a specified logical operation to the collection.
     * 
     * @param Logic $logic
     * @return self
     */
    public function applyNew(Logic $logic = Logic::AND): self
    {
        return $this->push(new Constraint($logic));
    }

    /**
     * Adds a comparison to the last constraint in the list.
     * 
     * @param Comparison<T> $comparison
     * @return self
     */
    public function append(Comparison $comparison): self
    {
        $this->constraints[count($this->constraints)-1]->push($comparison);
        return $this;
    }

    /**
     * Adds an equality comparison to the last constraint.
     * 
     * @param T $value
     * @param Logic $logic
     * @return self
     */
    public function equal($value, Logic $logic = Logic::AND): self
    {
        $this->append(new Comparison(Comparator::EQ, $value, $logic));
        return $this;
    }

    /**
     * Adds a between comparison to the last constraint
     * 
     * @param T $min            The minimum value of the range(not inclusive).
     * @param T $max            The maximum value of the range(not inclusive).
     * @param Logic $logic      The logic for this comparison.
     * @return self
     */
    public function between($min, $max, Logic $logic = Logic::AND): self
    {
        $this->append(new Comparison(Comparator::BETWEEN, [$min, $max], $logic));
        return $this;
    }

    /**
     * Adds a matching comparison to the last constraint based on a specified pattern.
     * 
     * @param T $value                   The value to check against the pattern.
     * @param MatchPattern $pattern      The pattern to match against the value.
     * @param Logic $logic               The logic for this comparison.
     * @return self
     */
    public function match($value, MatchPattern $pattern, Logic $logic = Logic::AND): self
    {
        $this->append(new Comparison(Comparator::MATCH, [$value, $pattern], $logic));
        return $this;
    }
}