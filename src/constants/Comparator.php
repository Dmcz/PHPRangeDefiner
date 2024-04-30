<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner\constants;

/**
 * comparison types for defining conditions
 */
enum Comparator
{
    case GT;        // Greater than.
    case GTE;       // Greater than or equal to.
    case LT;        // Less than.
    case LTE;       // Less than or equal to.
    case EQ;        // Equal to.
    case NEQ;       // Not equal to.
    case IN;        // Included in a specified set.
    case NOTIN;     // Not included in a specified set.
    case BETWEEN;   // Between two values, not inclusive.
    case INCLUSIVE; // Within two value, inclusive.
    case NULL;      // Is null.
    case NOTNULL;   // Is not null.
    case MATCH;     // Matches a value by specified pattern.
}