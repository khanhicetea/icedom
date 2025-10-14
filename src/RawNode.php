<?php

namespace IceTea\IceDOM;

/**
 * RawNode - Renders children as raw, unprocessed string concatenation.
 *
 * Responsibility:
 * This minimal Node extension outputs children exactly as stored in the internal
 * array without any processing, evaluation, or HTML escaping. It performs simple
 * string concatenation using PHP's implicit type conversion, bypassing all the
 * safety and transformation features of the base Node class.
 *
 * Key Differences from Node:
 * - Base Node: Evaluates closures, escapes HTML, processes types individually
 * - RawNode: Direct implode() on children array, no evaluation or escaping
 *
 * What RawNode DOES NOT Do:
 * - Evaluate closures (stored as Closure objects, converted to string by PHP)
 * - Recursively render Node children (results in object string representation)
 * - HTML-escape strings (potential XSS vulnerability)
 * - Handle types differently (all rely on PHP's implicit __toString())
 * - Set parent relationships
 * - Add separators between children
 *
 * Use Cases:
 * - Pre-escaped HTML content that should not be double-escaped
 * - Performance-critical scenarios where content is already safe
 * - Simple string concatenation without Node overhead
 * - Building raw content from trusted sources
 *
 * Security Warning:
 * Using RawNode with user-provided or untrusted content creates XSS vulnerabilities.
 * Only use with pre-sanitized, trusted, or static content. For individual safe
 * strings within a normal Node tree, use SafeStringable instead.
 *
 * Example:
 * ```php
 * $rawNode = new RawNode(['<div>', 'Hello World', '</div>']);
 * echo $rawNode; // Outputs: <div>Hello World</div>
 *
 * // Closures are NOT evaluated:
 * $raw2 = new RawNode([fn() => 'test']); // Outputs: Closure object string
 * ```
 *
 * @author IceTea Team
 *
 * @see Node Base class with HTML escaping and type handling
 * @see SafeStringable For marking individual strings as safe within normal Nodes
 * @see EchoNode For capturing output buffer content with evaluation
 */
class RawNode extends Node
{
    /**
     * Converts node to raw string by directly concatenating children.
     *
     * Overrides the parent __toString() to bypass all Node processing logic.
     * Simply calls implode('', $this->children) which:
     * - Concatenates all children with empty string separator
     * - Uses PHP's implicit __toString() conversion for objects
     * - Does NOT evaluate closures (they become "Closure Object")
     * - Does NOT recursively render Node children properly
     * - Does NOT escape HTML content
     *
     * Behavior by child type:
     * - string: Output as-is without escaping
     * - int/float: Converted to string by PHP
     * - Node: Results in "Object" or class name string (NOT rendered)
     * - Closure: Results in "Closure Object" string (NOT evaluated)
     * - Stringable: __toString() called by PHP implicitly
     * - null: Empty string contribution
     *
     * Warning: This is minimal concatenation. Most child types will NOT
     * render as expected compared to base Node behavior.
     *
     * @return string Raw concatenation of children using PHP's implicit string conversion
     */
    public function __toString()
    {
        return implode('', $this->children);
    }
}
