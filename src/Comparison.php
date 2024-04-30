<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner;

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
     * @param Comparator $comparator    The type of comparison to apply (e.g., EQ, LT).
     * @param T|array{0:T,1:T}|array{0:T,1:MatchPattern} $value                  The value to be used in the comparison.
     * @param Logic $logic              The logical relationship this comparison contributes to (e.g., AND, OR).
     */
    public function __construct(
        public readonly Comparator $comparator,
        public readonly Mixed $value,
        public readonly Logic $logic
    ){}
}
