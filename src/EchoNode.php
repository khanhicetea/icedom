<?php

namespace IceTea\IceDOM;

use Stringable;

use function ob_get_clean;
use function ob_start;

/**
 * EchoNode - Captures output buffer content from children for raw HTML rendering.
 *
 * Responsibility:
 * This specialized Node extension captures direct output (echo, print, etc.) from
 * children using PHP's output buffering mechanism. It's designed for integrating
 * legacy code, template systems, or components that output directly rather than
 * returning strings. Unlike the base Node which escapes string content, EchoNode
 * captures and returns raw output without HTML escaping.
 *
 * Key Differences from Node:
 * - Base Node: Escapes strings, concatenates return values
 * - EchoNode: Captures echoed output via ob_start(), no escaping applied
 *
 * Use Cases:
 * - Integrating legacy PHP templates that use echo/print
 * - Wrapping closures that output directly to stdout
 * - Mixing procedural output code with DOM tree structure
 * - Capturing output from third-party libraries
 *
 * Security Warning:
 * No HTML escaping is performed on captured output. Ensure child components
 * properly sanitize their output to prevent XSS vulnerabilities.
 *
 * Example:
 * ```php
 * $echoNode = new EchoNode([
 *     function() {
 *         echo '<div>Hello</div>';
 *         return '<span>World</span>';  // Also captured
 *     }
 * ]);
 * echo $echoNode; // Outputs: <div>Hello</div><span>World</span>
 * ```
 *
 * @author IceTea Team
 *
 * @see Node Base class with HTML escaping
 * @see RawNode For raw string concatenation without evaluation
 */
class EchoNode extends Node
{
    /**
     * Converts node to string by capturing output buffer from evaluated children.
     *
     * Overrides the parent childrenToString() to use output buffering instead of
     * string concatenation. This captures both echoed content and return values
     * from children without HTML escaping.
     *
     * Processing flow:
     * 1. Starts output buffering with ob_start()
     * 2. Evaluates each child via tryEvalClosure() (executes closures)
     * 3. Echoes evaluated results based on type
     * 4. Captures all buffered output with ob_get_clean()
     * 5. Returns captured content (empty string if nothing captured)
     *
     * Type handling after evaluation:
     * - Node: Converted to string via __toString() and echoed
     * - SafeStringable: Converted to string and echoed without escaping
     * - string: Echoed directly without escaping
     * - int|float: Echoed directly as numeric output
     * - Stringable: Converted via __toString() and echoed
     * - Other types: Ignored
     *
     * Security Note: No HTML escaping applied - output is raw.
     *
     * @return string The captured output buffer content, empty string if no output
     */
    protected function childrenToString(): string
    {
        ob_start();
        foreach ($this->children as $child) {
            $evaluatedChild = $this->tryEvalClosure($child);

            if ($evaluatedChild instanceof Node) {
                echo $evaluatedChild->__toString();
            } elseif ($evaluatedChild instanceof SafeStringable) {
                echo $evaluatedChild->__toString();
            } elseif (is_string($evaluatedChild)) {
                echo $evaluatedChild;
            } elseif (is_int($evaluatedChild) || is_float($evaluatedChild)) {
                echo $evaluatedChild;
            } elseif ($evaluatedChild instanceof Stringable) {
                echo $evaluatedChild->__toString();
            }
        }
        $raw = ob_get_clean() ?: '';

        return $raw;
    }
}
