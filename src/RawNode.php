<?php

namespace IceTea\IceDOM;

/**
 * A specialized Node that renders raw, unescaped content directly.
 *
 * The RawNode class extends the base Node functionality by overriding the
 * __toString() method to output children content without any HTML escaping
 * or processing. It simply concatenates all children as-is.
 *
 * Unlike the base Node class which:
 * - HTML-escapes string content for security
 * - Joins children with spaces
 * - Evaluates closures and processes various content types
 *
 * RawNode provides a minimal implementation that:
 * - Outputs children exactly as they exist in the internal array
 * - Performs no HTML escaping
 * - Performs no content type handling
 * - Joins children with empty string (no separators)
 *
 * This class is useful when you need to output content that is already
 * pre-processed, pre-escaped, or when you want complete control over the
 * raw output without any automatic transformations.
 *
 * WARNING: Using RawNode with user-provided content can introduce XSS
 * vulnerabilities if the content is not properly sanitized beforehand.
 * Only use RawNode with trusted or pre-escaped content.
 *
 * Example usage:
 * ```php
 * $rawNode = new RawNode(['<div>', 'Hello World', '</div>']);
 * echo $rawNode; // Output: '<div>Hello World</div>'
 * ```
 *
 * @see Node The base class with HTML escaping and content processing
 * @see SafeString For marking individual strings as safe from escaping
 * @see EchoNode For capturing output buffer content
 */
class RawNode extends Node
{
    /**
     * Converts the node to its string representation by concatenating children.
     *
     * This method overrides the parent Node's implementation to provide
     * minimal, direct concatenation of all children without any processing,
     * evaluation, or HTML escaping.
     *
     * The children array is joined using an empty string separator, meaning
     * all children are concatenated directly without any spaces or other
     * delimiters between them.
     *
     * NOTE: This method does not:
     * - Evaluate closures
     * - Process Node instances
     * - HTML-escape strings
     * - Handle different content types
     * - Add any separators between children
     *
     * All children are output exactly as they exist in the internal array,
     * relying on PHP's implicit string conversion for non-string types.
     *
     * @return string The raw concatenation of all children
     */
    public function __toString()
    {
        return implode('', $this->children);
    }
}
