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
     * $valueHandler = function($value, $name) {
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
    ) {
    }

    /**
     * Build query from condition.
     *
     * @param Condition $condition Condition object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromCondition(Condition $condition, $query)
    {
        $query->where(boolean: $condition->logic->value, column: function ($query) use ($condition) {
            foreach ($condition->criteria() as $criteria) {
                if ($criteria instanceof Condition) {
                    $this->buildQueryFromCondition($criteria, $query);
                } else {
                    $this->buildQueryFromRange($criteria, $query);
                }
            }
        });
    }

    /**
     * Build query from range.
     *
     * @param Range $range Range object
     * @param mixed $query The query builder object (e.g., Laravel's Eloquent/Query builder)
     */
    public function buildQueryFromRange(Range $range, $query): void
    {
        $query->where(boolean: $range->logic->value, column: function ($query) use ($range) {
            foreach ($range->getConstraints() as $constraint) {
                $query->where(boolean: $constraint->logic->value, column: function ($query) use ($range, $constraint) {
                    foreach ($constraint->getComparisons() as $comparison) {
                        self::buildQueryFromComparison($range->name, $comparison, $query);
                    }
                });
            }
        });
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
        switch ($comparison->comparator) {
            case Comparator::EQ:
                $query->where($this->ensureName($name), '=', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::NEQ:
                $query->where($this->ensureName($name), '<>', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::GT:
                $query->where($this->ensureName($name), '>', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::GTE:
                $query->where($this->ensureName($name), '>=', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::LT:
                $query->where($this->ensureName($name), '<', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::LTE:
                $query->where($this->ensureName($name), '<=', $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::IN:
                $query->whereIn($this->ensureName($name), $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value);
                break;
            case Comparator::NOTIN:
                $query->whereIn($this->ensureName($name), $this->ensureValue($name, $comparison->getValue()), $comparison->logic->value, true);
                break;
            case Comparator::NULL:
                $query->whereNull($this->ensureName($name), $comparison->logic->value);
                break;
            case Comparator::NOTNULL:
                $query->whereNull($this->ensureName($name), $comparison->logic->value, true);
                break;
            case Comparator::MATCH:
                $expression = match ($comparison->getMatchPattern()) {
                    MatchPattern::CONTAIN => '%' . $this->ensureValue($name, $comparison->getValue()) . '%',
                    MatchPattern::START_WITH => $this->ensureValue($name, $comparison->getValue()) . '%',
                    MatchPattern::END_WITH => '%' . $this->ensureValue($name, $comparison->getValue()),
                    default => throw new UnexpectedValueException('The match pattern not support.')
                };

                $query->where($this->ensureName($name), 'like', $expression);
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
     *
     * @param string $name Field name
     * @param mixed $value Field value
     * @return mixed Processed field value
     */
    public function ensureValue(string $name, $value): mixed
    {
        if ($this->valueHandler) {
            return call_user_func($this->valueHandler, $value, $name);
        }

        return $value;
    }
}
