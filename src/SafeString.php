<?php

namespace IceTea\IceDOM;

/**
 * Represents a string that is marked as safe and should not be escaped during rendering.
 *
 * This class wraps a string value to indicate that it contains safe content (e.g., pre-sanitized HTML)
 * that should be rendered as-is without additional escaping or encoding.
 */
class SafeString
{
    /**
     * Creates a new SafeString instance.
     *
     * @param string $value The safe string value to wrap
     */
    public function __construct(
        protected string $value = ''
    ) {}

    /**
     * Returns the wrapped string value.
     *
     * @return string The safe string value
     */
    public function __toString()
    {
        return $this->value;
    }
}
