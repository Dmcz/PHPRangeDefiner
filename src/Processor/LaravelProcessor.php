<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner\Processor;

use Closure;
use Dmcz\RangeDefiner\Range;
use UnexpectedValueException;
use Dmcz\RangeDefiner\Condition;
use Dmcz\RangeDefiner\Comparison;
use Dmcz\RangeDefiner\Constants\Comparator;
use Dmcz\RangeDefiner\Constants\MatchPattern;
use Dmcz\RangeDefiner\Constraint;

class LaravelProcessor
{
    /**
     * @param ?callable $nameHandler The handler for field names in the where condition. Example: function(string $name): string
     * @param ?callable $valueHandler The handler for field values in the where condition. Example: function(mixed $value, string $name): mixed
     *
     * @example
     * $nameHandler = function($name) {
     *     return 'prefix_' . $name;
     * };
     *
     * $valueHandler = function($name, $value) {
     *     if ($name === 'age') {
     *         return (int) $value;
     *     }
     *
     *     if ($value instanceof DataTime){
     *         return $value->format(DateTime::ATOM);
     *     }
     *     return $value;
     * };
     *
     * $processor = new LaravelProcessor($nameHandler, $valueHandler);
     */
    public function __construct(
        public readonly ?Closure $nameHandler = null,
        public readonly ?Closure $valueHandler = null
    ) {}

    /**
     * Build query from condition.
     *
     * @param Condition $condition Condition object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromCondition(Condition $condition, $query)
    {
        $criterias = $condition->criteria();

        if(count($criterias) == 1){
            $criteria = current($criterias);
            if ($criteria instanceof Condition) {
                $this->buildQueryFromCondition($criteria, $query);
            } else {
                $this->buildQueryFromRange($criteria, $query);
            }

        }else if(count($criterias) > 1){
            $query->where(boolean: $condition->logic->value, column: function ($query) use ($condition, $criterias) {
                foreach ($criterias as $criteria) {
                    if ($criteria instanceof Condition) {
                        $this->buildQueryFromCondition($criteria, $query);
                    } else {
                        $this->buildQueryFromRange($criteria, $query);
                    }
                }
            });
        }        

    }

    /**
     * Build query from range.
     *
     * @param Range $range Range object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromRange(Range $range, $query): void
    {
        $constraints = $range->getConstraints();

        if(count($constraints) == 1){
            $this->buildQueryFromConstraint($range->name, current($constraints), $query);

        }else if(count($constraints) > 1){
            $query->where(boolean: $range->logic->value, column: function ($query) use ($constraints, $range) {
                foreach ($constraints as $constraint) {
                    $this->buildQueryFromConstraint($range->name, current($constraints), $query);
                }
            });
        }
    }

    /**
     * Build query from constraint.
     *
     * @param string $name The name of constraint.
     * @param Constraint $range Constraint object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromConstraint(string $name, Constraint $constraint, $query): void
    {
        $comparisons = $constraint->getComparisons();

        if(count($comparisons) == 1){
            $this->buildQueryFromComparison($name, current($comparisons), $query);
        }else if(count($comparisons) > 1){
            $query->where(boolean: $constraint->logic->value, column: function ($query) use ($name, $comparisons) {
                foreach ($comparisons as $comparison) {
                    $this->buildQueryFromComparison($name, $comparison, $query);
                }
            });
        }
    }

    /**
     * Build query from comparison.
     *
     * @param string $name Comparison name
     * @param Comparison $comparison Comparison object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromComparison(string $name, Comparison $comparison, $query): void
    {
        $ensuredName = $this->ensureName($name);
        $ensuredValue = $this->ensureValue($name, $comparison);

        if($ensuredValue instanceof Closure){
            call_user_func($ensuredValue, $query, $ensuredName, $comparison);
            return;
        }

        switch ($comparison->comparator) {
            case Comparator::EQ:
                $query->where($ensuredName, '=', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::NEQ:
                $query->where($ensuredName, '<>', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::GT:
                $query->where($ensuredName, '>', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::GTE:
                $query->where($ensuredName, '>=', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::LT:
                $query->where($ensuredName, '<', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::LTE:
                $query->where($ensuredName, '<=', $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::IN:
                $query->whereIn($ensuredName, $ensuredValue, $comparison->logic->value);
                break;
            case Comparator::NOTIN:
                $query->whereIn($ensuredName, $ensuredValue, $comparison->logic->value, true);
                break;
            case Comparator::NULL:
                $query->whereNull($ensuredName, $comparison->logic->value);
                break;
            case Comparator::NOTNULL:
                $query->whereNull($ensuredName, $comparison->logic->value, true);
                break;
            case Comparator::MATCH:
                $expression = match ($comparison->getMatchPattern()) {
                    MatchPattern::CONTAIN => '%' . $ensuredValue . '%',
                    MatchPattern::START_WITH => $ensuredValue . '%',
                    MatchPattern::END_WITH => '%' . $ensuredValue,
                    default => throw new UnexpectedValueException('The match pattern not support.')
                };

                $query->where($ensuredName, 'like', $expression);
                break;
            default:
                throw new UnexpectedValueException('The comparator not support.');
        }
    }

    /**
     * Ensure the field name in where condition is processed.
     *
     * @param string $name Field name
     * @return string Processed field name
     */
    public function ensureName(string $name): string
    {
        if ($this->nameHandler) {
            return call_user_func($this->nameHandler, $name);
        }

        return $name;
    }

    /**
     * Ensure the field value in where condition is processed.
     */
    public function ensureValue(string $name, Comparison $comparison): mixed
    {
        if ($this->valueHandler) {
            switch($comparison->comparator){
                case Comparator::IN:
                case Comparator::NOTIN:
                    $arr = [];

                    foreach($comparison->getValue() as $value){
                        $arr[] = call_user_func($this->valueHandler, $name, $value);
                    }

                    return $arr;

                default:
                    return call_user_func($this->valueHandler, $name, $comparison->getValue());
            }

        }

        return $comparison->getValue();
    }
}
