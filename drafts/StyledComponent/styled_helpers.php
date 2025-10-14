<?php

/**
 * Styled Component Helper Functions
 *
 * This file provides helper functions for creating styled components with scoped CSS.
 * Components register their styles with the global StyleRegistry and receive unique
 * scope class names to prevent style collisions.
 *
 * @package IceTea\IceDOM
 */

namespace IceTea\IceDOM;

use Closure;

/**
 * Creates a styled component factory function.
 *
 * Generates a callable that creates HTML elements with scoped CSS styles.
 * The styles are automatically registered with the StyleRegistry and a unique
 * scope class is applied to prevent style collisions.
 *
 * The returned callable accepts the same arguments as the original HTML tag
 * function (e.g., _div, _span) and automatically applies the scope class.
 *
 * CSS Rules Format:
 * - Top-level keys are CSS properties or nested selectors
 * - Nested selectors support '&' for parent reference
 * - Properties are key-value pairs (property => value)
 *
 * Nested CSS Examples:
 * - '&:hover' => Pseudo-selector on component root
 * - '& .child' => Descendant selector
 * - '&.active' => Modifier class
 *
 * Example:
 * ```php
 * $StyledButton = styled('button', [
 *     'padding' => '0.5rem 1rem',
 *     'background' => '#007bff',
 *     'color' => 'white',
 *     '&:hover' => [
 *         'background' => '#0056b3'
 *     ]
 * ]);
 *
 * // Usage
 * echo $StyledButton(['Click Me']);
 * // Outputs: <button class="c-a3f8b9c4">Click Me</button>
 * ```
 *
 * @param string $tag The HTML tag name (div, span, button, etc.)
 * @param array $cssRules Nested array of CSS rules and properties
 * @return Closure Factory function that creates styled HTML nodes
 *                 Signature: function(mixed $firstArg = null, ?array $children = null): HtmlNode
 */
function styled(string $tag, array $cssRules): Closure
{
    // Generate hash based on tag and rules for deduplication
    $componentHash = md5($tag.serialize($cssRules));
    $registry = StyleRegistry::getInstance();
    $scopeClass = $registry->register($componentHash, $cssRules);

    // Return factory function that creates nodes with scope class
    return function (mixed $firstArg = null, ?array $children = null) use ($tag, $scopeClass) {
        // Create the base node using the tag helper (in global namespace)
        $helperFunction = "_$tag";
        if (! function_exists($helperFunction)) {
            throw new \Exception("HTML tag helper function _$tag does not exist");
        }

        $node = $helperFunction($firstArg, $children);
        
        // Add the scope class while preserving existing classes
        if ($node instanceof HtmlNode) {
            $existingClass = $node->getAttribute('class', '');
            if ($existingClass) {
                $node->setAttribute('class', $existingClass.' '.$scopeClass);
            } else {
                $node->classes($scopeClass);
            }
        }

        return $node;
    };
}

/**
 * Outputs all collected CSS styles as a <style> tag.
 *
 * Compiles all styles registered with the StyleRegistry into a single
 * CSS stylesheet. Should be called once in the <head> section of your document.
 *
 * This function retrieves all styles from the registry, compiles them into
 * proper CSS syntax with scoped selectors, and wraps them in a <style> tag.
 *
 * Example:
 * ```php
 * echo _html('lang="en"', [
 *     _head([
 *         _title(['My App']),
 *         _styles()  // Output all collected styles
 *     ]),
 *     _body([
 *         $StyledComponent(['Content'])
 *     ])
 * ]);
 * ```
 *
 * @param bool $minify Whether to minify the CSS output (default: false)
 *                     When true, removes whitespace and comments for production
 * @return HtmlNode A <style> element containing all compiled CSS
 */
function _styles(bool $minify = false): HtmlNode
{
    $css = StyleRegistry::getInstance()->compile($minify);

    return _style([_safe($css)]);
}

/**
 * Creates a styled HTML element directly with inline style definition.
 *
 * Convenience function that combines element creation with style registration
 * in a single call. Returns an HtmlNode with the scope class already applied.
 *
 * This is useful for one-off styled elements where you don't need a reusable
 * component factory.
 *
 * Example:
 * ```php
 * $card = _styled('div', [
 *     'padding' => '1rem',
 *     'background' => 'white',
 *     '& .title' => [
 *         'font-weight' => 'bold'
 *     ]
 * ], ['class' => 'card'], [
 *     _div(['class' => 'title'], ['My Card']),
 *     _div(['Body content'])
 * ]);
 * ```
 *
 * @param string $tag The HTML tag name
 * @param array $cssRules CSS rules for scoped styling
 * @param mixed $firstArg Attributes or content for the element
 * @param array|null $children Child elements (optional)
 * @return HtmlNode The styled HTML element
 */
function _styled(string $tag, array $cssRules, mixed $firstArg = null, ?array $children = null): HtmlNode
{
    $styledFactory = styled($tag, $cssRules);

    return $styledFactory($firstArg, $children);
}

