<?php

namespace IceTea\IceDOM;

use function base_convert;
use function intval;

/**
 * HtmlRef - Generates unique reference identifiers for HTML elements.
 *
 * Responsibility:
 * This utility class generates and manages unique string identifiers suitable for
 * HTML element IDs, keys, or any scenario requiring unique reference strings. It
 * uses a static counter initialized from the current timestamp to ensure uniqueness
 * across multiple instances within the same request. Each instance automatically
 * generates a base-36 encoded identifier, or accepts a custom reference string.
 *
 * Uniqueness Strategy:
 * - Static counter starts at current timestamp in milliseconds (first instance)
 * - Counter increments with each new instance
 * - Counter converted to base-36 for compact string representation
 * - Prefixed with underscore to ensure valid HTML ID format
 *
 * Generated Format:
 * - Pattern: _{base36_counter}
 * - Example: _1a2b3c, _1a2b3d, _1a2b3e
 * - Always starts with underscore (valid CSS selector start)
 * - Uses 0-9, a-z characters (36 possible characters per position)
 *
 * Use Cases:
 * - Generating unique IDs for dynamically created HTML elements
 * - Creating unique keys for list items in loops
 * - Reference identifiers for DOM manipulation
 * - Ensuring uniqueness in component libraries
 *
 * Example:
 * ```php
 * $ref1 = new HtmlRef(); // Auto-generated: _1a2b3c
 * $ref2 = new HtmlRef(); // Auto-generated: _1a2b3d
 * $ref3 = new HtmlRef('custom-id'); // Custom: custom-id
 *
 * $div = div()->id((string) $ref1); // <div id="_1a2b3c">
 * ```
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 */
class HtmlRef
{
    /**
     * Static counter for generating unique identifiers across all instances.
     *
     * Lifecycle:
     * - Initialized to 0 at script start
     * - Set to timestamp (milliseconds) on first HtmlRef instantiation
     * - Incremented by 1 for each new instance
     * - Persists across instances within same request
     * - Reset on each new request (PHP request lifecycle)
     *
     * @var int Counter value, becomes timestamp on first use, then increments
     */
    public static int $base = 0;

    /**
     * Create a new HtmlRef with auto-generated or custom reference.
     *
     * Constructor behavior:
     * 1. Increments static $base (initializes to timestamp if first instance)
     * 2. If custom $ref provided: Uses it as-is
     * 3. If $ref empty: Generates _{base36} from incremented counter
     *
     * Counter initialization (first instance only):
     * - microtime(true) returns float seconds with microseconds
     * - Multiplied by 1000 for milliseconds
     * - Cast to int for counter base
     * - Subsequent instances just increment from there
     *
     * @param string $ref Optional custom reference identifier.
     *                    - Non-empty string: Used directly as the reference
     *                    - Empty string (default): Auto-generates _{base36_counter}
     *                    - Custom values not validated (ensure they're valid HTML IDs if needed)
     */
    public function __construct(private string $ref = '')
    {
        HtmlRef::$base = (HtmlRef::$base ?: intval(microtime(true) * 1000)) + 1;
        $this->ref = $ref ?: '_'.base_convert(HtmlRef::$base, 10, 36);
    }

    /**
     * Converts HtmlRef to its string representation.
     *
     * Returns the reference identifier when the object is used in string context,
     * such as concatenation, echo, or type casting.
     *
     * @return string The reference identifier (auto-generated or custom)
     */
    public function __toString()
    {
        return $this->ref;
    }
}
