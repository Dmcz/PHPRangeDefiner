<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

use Dmcz\RangeDefiner\constants\Logic;

/**
 * Manages a collection of comparison conditions, associating them with a logical relationship.
 * 
 * @template T
 */
class Constraint
{
    /**
     * collection of comparisons
     *
     * @var Comparison<T>[]
     */
    protected array $comparisons;

    public function __construct(
        public readonly Logic $logic = Logic::AND
    ){
        $this->comparisons = [];
    }

    /**
     * get comparisons
     *
     * @return Comparison<T>[]
     */
    public function getComparisons(): array
    {
        return $this->comparisons;
    }

    /**
     * Adds a new comparison condition to the collection.
     * 
     * @param Comparison<T> $comparison
     * @return void
     */
    public function push(Comparison $comparison): void
    {
        $this->comparisons[] = $comparison;
    }
}