<?php

namespace IceTea\IceDOM;

/**
 * SafeString - Marks string content as pre-sanitized to bypass HTML escaping.
 *
 * Responsibility:
 * This wrapper class designates string content as safe/trusted, signaling to the
 * Node rendering system that the content should NOT be HTML-escaped during output.
 * It's used when you have pre-sanitized HTML, trusted markup, or content that has
 * already been properly escaped and should be rendered as-is.
 *
 * How It Works:
 * - When Node.childrenToString() encounters a SafeString, it outputs the content
 *   directly without passing it through htmlspecialchars()
 * - Regular strings are escaped for XSS protection
 * - SafeString bypasses this escaping
 *
 * Use Cases:
 * - Rendering pre-sanitized HTML from a trusted sanitization library
 * - Including validated HTML from a WYSIWYG editor
 * - Embedding trusted markup (e.g., icons, formatted text)
 * - Output from template engines that handle their own escaping
 * - HTML entities that should not be double-encoded
 *
 * Security Considerations:
 * - ONLY use with content you trust or have manually sanitized
 * - Improper use can introduce XSS vulnerabilities
 * - Prefer normal strings (auto-escaped) unless you have a specific reason
 * - Document why content is marked safe in your code
 *
 * Example:
 * ```php
 * $node = new Node([
 *     '<script>alert("XSS")</script>',  // Escaped: &lt;script&gt;...
 *     new SafeString('<em>Safe HTML</em>'),  // Not escaped: <em>Safe HTML</em>
 * ]);
 * ```
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 * @see Node::childrenToString() Where SafeString exemption is applied
 * @see RawNode For rendering entire subtrees without escaping
 */
class SafeString
{
    /**
     * Creates a new SafeString wrapper for trusted content.
     *
     * Warning: Only wrap content that is genuinely safe. This constructor does
     * not perform any sanitization - it simply marks the string for unescaped output.
     *
     * @param string $value The pre-sanitized/trusted string content to wrap.
     *                      - HTML markup: Will be rendered as-is without escaping
     *                      - Entities: Will not be double-encoded (e.g., &amp; stays as &amp;)
     *                      - Scripts/tags: Will execute/render if present (security risk!)
     *                      - Empty string: Default, produces no output
     */
    public function __construct(
        protected string $value = ''
    ) {}

    /**
     * Returns the wrapped string value without modification.
     *
     * This magic method is called when SafeString is used in string context.
     * The Node class checks for SafeString instances and outputs this value
     * directly without HTML escaping.
     *
     * @return string The unescaped string value exactly as stored
     */
    public function __toString()
    {
        return $this->value;
    }
}
