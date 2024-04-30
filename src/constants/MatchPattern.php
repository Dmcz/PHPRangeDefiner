<?php

declare(strict_types=1);

namespace Dmcz\RangeDefiner\constants;

/**
 * types of matching patterns
 */
enum MatchPattern
{
    case START_WITH;  // Matches values starting with the specified.
    case END_WITH;    // Matches values ending with the specified.
    case CONTAIN;     // Contains the specified pattern within the value.
}