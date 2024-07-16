<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner\Constants;

/**
 * logical operations for combining multiple conditions.
 */
enum Logic: string
{
    case AND = 'and';
    case OR = 'or';
}
