<?php

namespace IceTea\IceDOM;

use function implode;

/**
 * HtmlDocument - Represents a complete HTML5 document with DOCTYPE declaration.
 *
 * Responsibility:
 * This specialized HtmlNode extension generates a complete HTML5 document by
 * prepending the DOCTYPE declaration to the rendered HTML element. It's designed
 * to be used as the root element for full HTML pages, ensuring proper HTML5
 * document structure.
 *
 * Key Difference from HtmlNode:
 * - HtmlNode: Renders just the HTML element: <html>...</html>
 * - HtmlDocument: Renders DOCTYPE + HTML element: <!DOCTYPE html>\n<html>...</html>
 *
 * Usage:
 * Typically instantiated as an <html> tag and used as the document root:
 * ```php
 * $doc = new HtmlDocument([
 *     head([...]),
 *     body([...])
 * ], 'html');
 * ```
 *
 * Output Format:
 * - Line 1: <!DOCTYPE html>
 * - Line 2+: The rendered HTML element with all attributes and children
 * - Newline separator between DOCTYPE and HTML element
 *
 * Example:
 * ```php
 * $doc = HtmlDocument::tag('html', null, [
 *     head([title('My Page')]),
 *     body(['Hello World'])
 * ]);
 * echo $doc;
 * // Outputs:
 * // <!DOCTYPE html>
 * // <html><head><title>My Page</title></head><body>Hello World</body></html>
 * ```
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 * @see HtmlNode Parent class for HTML element rendering
 */
class HtmlDocument extends HtmlNode
{
    /**
     * Converts the HTML document to string with DOCTYPE declaration.
     *
     * Overrides parent __toString() to prepend the HTML5 DOCTYPE declaration.
     * The DOCTYPE and HTML element are joined with a newline character.
     *
     * Output structure:
     * 1. <!DOCTYPE html> - HTML5 DOCTYPE declaration
     * 2. \n - Newline separator
     * 3. parent::__toString() - The rendered HTML element
     *
     * @return string Complete HTML5 document with DOCTYPE and rendered HTML element
     */
    public function __toString()
    {
        return implode("\n", [
            '<!DOCTYPE html>',
            parent::__toString(),
        ]);
    }
}
