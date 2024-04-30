<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner\constants;

/**
 * logical operations for combining multiple conditions.
 */
enum Logic
{
    case AND;
    case OR;
}