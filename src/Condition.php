<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use Dmcz\RangeDefiner\Constants\Logic;
use UnexpectedValueException;

class Condition
{
    /**
     * Comparisons  with a specified name.
     *
     * @var array<string:mixed>
     */
    protected array $comparisons = [];

    /**
     * Range conditions.
     *
     * @var Range[]
     */
    protected array $ranges = [];

    /**
     * Sub-conditions.
     *
     * @var Condition[]
     */
    protected array $conditions = [];

    public function __construct(
        public readonly Logic $logic = Logic::AND
    ) {
    }

    public function __set($name, $value): void
    {
        $this->setComparison($name, $value);
    }

    public function __get($name)
    {
        return $this->getComparison($name);
    }

    public function __isset($name)
    {
        return $this->hasComparison($name);
    }

    /**
     * get all criteria of this condition.
     *
     * @return array<Condition|Range>
     */
    public function criteria(): array
    {
        $criteria = array_merge($this->ranges, $this->conditions);

        // Convert the comparison to a range condition and then append it to the criteria
        foreach ($this->comparisons as $name => $comparison) {
            if (! $comparison instanceof Comparison) {
                $comparison = Comparison::equal($comparison);
            }

            $criteria[] = (new Range($name))->append($comparison);
        }

        return $criteria;
    }

    /**
     * Set a comparison with a specified name.
     */
    public function setComparison(string $name, mixed $comparison): self
    {
        $this->comparisons[$name] = $comparison;
        return $this;
    }

    /**
     * Get a comparison by name.
     */
    public function getComparison(string $name): mixed
    {
        return $this->comparisons[$name];
    }

    /**
     * Check whether a comparison for the specified name exists.
     */
    public function hasComparison(string $name): bool
    {
        return array_key_exists($name, $this->comparisons);
    }

    /**
     * Add a range condition.
     */
    public function addRange(Range $range): self
    {
        $this->ranges[] = $range;
        return $this;
    }

    /**
     * Add a sub-condition.
     *
     * @param Condition $condition The sub-condition. Cannot add itself as a sub-condition.
     */
    public function addCondition(Condition $condition): self
    {
        if ($condition === $this) {
            throw new UnexpectedValueException('Cannot add the condition to itself to avoid nested references.');
        }

        $this->conditions[] = $condition;
        return $this;
    }
}
