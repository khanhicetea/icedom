<?php

namespace IceTea\IceDOM;

use function base_convert;
use function intval;

/**
 * HtmlRef represents a unique HTML reference identifier.
 *
 * This class generates and manages unique reference strings that can be used
 * as HTML element identifiers, keys, or other reference purposes. It automatically
 * generates unique identifiers based on a base counter that increments with each
 * instance creation.
 *
 * @author IceTea Team
 */
class HtmlRef
{
    /**
     * Static base counter used to generate unique identifiers.
     *
     * This counter is incremented for each new instance and serves as the
     * foundation for generating unique reference strings. It's initialized
     * with the current timestamp in milliseconds when the first instance is created.
     */
    public static int $base = 0;

    /**
     * Create a new HtmlRef instance.
     *
     * If no reference is provided, a unique reference will be automatically
     * generated using the base counter converted to base-36 format. The generated
     * reference will start with an underscore followed by the base-36 representation
     * of the current base value.
     *
     * @param  string  $ref  Optional custom reference string. If empty, a unique
     *                       reference will be generated automatically.
     */
    public function __construct(private string $ref = '')
    {
        HtmlRef::$base = (HtmlRef::$base ?: intval(microtime(true) * 1000)) + 1;
        $this->ref = $ref ?: '_'.base_convert(HtmlRef::$base, 10, 36);
    }

    /**
     * Convert the HtmlRef to its string representation.
     *
     * Returns the reference string value when the object is cast to string
     * or used in string contexts.
     *
     * @return string The reference string
     */
    public function __toString()
    {
        return $this->ref;
    }
}
