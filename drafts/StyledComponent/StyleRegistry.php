<?php

namespace IceTea\IceDOM;

/**
 * StyleRegistry - Global singleton registry for managing scoped CSS styles.
 *
 * Responsibility:
 * This class provides a centralized registry for collecting and compiling CSS styles
 * from components throughout the application. It generates unique scope identifiers,
 * prevents style duplication, supports nested CSS syntax, and can compile all styles
 * into a single optimized stylesheet.
 *
 * Key Features:
 * - Singleton pattern for global style management
 * - Automatic deduplication based on style content hashing
 * - Nested CSS support (SCSS-like syntax with & parent selector)
 * - CSS minification option for production
 * - Scope identifier generation and mapping
 *
 * Usage Flow:
 * 1. Components register their styles via register()
 * 2. Registry generates unique scope class names
 * 3. compile() generates final CSS with all registered styles
 * 4. reset() clears registry for fresh rendering (useful in testing/SSR)
 *
 * Example:
 * ```php
 * $registry = StyleRegistry::getInstance();
 * $scopeClass = $registry->register('component-key', [
 *     'padding' => '1rem',
 *     '& .title' => ['color' => 'blue']
 * ]);
 * echo $registry->compile(); // CSS output
 * ```
 */
class StyleRegistry
{
    /**
     * Singleton instance of the registry.
     */
    private static ?self $instance = null;

    /**
     * Registered styles mapped by scope identifier.
     *
     * Structure: ['scope-id' => ['selector' => 'value', ...], ...]
     *
     * @var array<string, array>
     */
    private array $styles = [];

    /**
     * Mapping from component hash to assigned scope identifier.
     *
     * Enables deduplication - identical styles get same scope class.
     * Structure: ['hash' => 'scope-id', ...]
     *
     * @var array<string, string>
     */
    private array $scopeMap = [];

    /**
     * Private constructor to enforce singleton pattern.
     */
    private function __construct()
    {
        // Private to prevent direct instantiation
    }

    /**
     * Gets the singleton instance of the StyleRegistry.
     *
     * Creates the instance on first call, returns existing instance on subsequent calls.
     *
     * @return self The singleton StyleRegistry instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Registers CSS rules for a component and returns its scope class name.
     *
     * If the exact same rules have been registered before (based on content hash),
     * returns the existing scope identifier to enable style deduplication.
     * Otherwise, creates a new scope identifier and stores the rules.
     *
     * @param  string  $componentHash  Unique identifier for the component (typically content hash)
     * @param  array  $rules  CSS rules in nested array format
     *                        Example: ['color' => 'red', '& .child' => ['margin' => '1rem']]
     * @return string The scope class name (e.g., 'c-a3f8b9c4')
     */
    public function register(string $componentHash, array $rules): string
    {
        if (isset($this->scopeMap[$componentHash])) {
            return $this->scopeMap[$componentHash];
        }

        $scopeId = 'c-'.substr($componentHash, 0, 8);
        $this->scopeMap[$componentHash] = $scopeId;
        $this->styles[$scopeId] = $rules;

        return $scopeId;
    }

    /**
     * Compiles all registered styles into a single CSS string.
     *
     * Processes all registered styles, expands nested selectors, and optionally
     * minifies the output for production use.
     *
     * @param  bool  $minify  Whether to minify the CSS output (remove whitespace, comments)
     * @return string Complete CSS stylesheet with all scoped styles
     */
    public function compile(bool $minify = false): string
    {
        $css = '';
        foreach ($this->styles as $scopeId => $rules) {
            $css .= $this->compileRules($rules, ".{$scopeId}");
        }

        return $minify ? $this->minify($css) : $css;
    }

    /**
     * Recursively compiles CSS rules with nested selector support.
     *
     * Processes nested array structures and expands them into flat CSS rules.
     * Supports & parent selector for advanced selector composition and media queries.
     *
     * @param  array  $rules  Nested CSS rules array
     * @param  string  $context  Current selector context (e.g., '.c-a3f8b9c4')
     * @return string Compiled CSS rules for this context
     */
    private function compileRules(array $rules, string $context): string
    {
        $output = '';
        $props = [];
        $mediaQueries = [];

        foreach ($rules as $selector => $value) {
            if (is_array($value)) {
                // Check if this is a media query
                if (str_starts_with($selector, '@media')) {
                    // Store media queries separately to output them at the end
                    $mediaQueries[$selector] = $value;
                } else {
                    // Nested rule - recurse with resolved selector
                    $fullSelector = $this->resolveSelector($selector, $context);
                    $output .= $this->compileRules($value, $fullSelector);
                }
            } else {
                // Simple property
                $props[] = "$selector: $value";
            }
        }

        // Output properties for current context if any exist
        if (! empty($props)) {
            $output = "$context { ".implode('; ', $props)."; }\n".$output;
        }

        // Process media queries
        foreach ($mediaQueries as $mediaQuery => $mediaRules) {
            $mediaContent = $this->compileRules($mediaRules, $context);
            $output .= "$mediaQuery {\n$mediaContent}\n";
        }

        return $output;
    }

    /**
     * Resolves a selector string within the current context.
     *
     * Handles different selector patterns:
     * - '&' (parent reference): Replaced with context
     * - '& .child': Becomes '.context .child'
     * - '&:hover': Becomes '.context:hover'
     * - '.child': Becomes '.context .child' (descendant)
     *
     * @param  string  $selector  Selector to resolve (may contain &)
     * @param  string  $context  Current selector context
     * @return string Fully resolved selector
     */
    private function resolveSelector(string $selector, string $context): string
    {
        if (strpos($selector, '&') !== false) {
            return str_replace('&', $context, $selector);
        }

        return "$context $selector";
    }

    /**
     * Minifies CSS by removing unnecessary whitespace and comments.
     *
     * Performs the following optimizations:
     * - Removes CSS comments
     * - Removes newlines and extra whitespace
     * - Removes spaces around CSS syntax characters
     *
     * @param  string  $css  CSS string to minify
     * @return string Minified CSS string
     */
    private function minify(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove newlines and carriage returns
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        // Collapse multiple spaces to single space
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove spaces around CSS syntax characters
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);

        return trim($css);
    }

    /**
     * Resets the registry by clearing all registered styles and mappings.
     *
     * Useful for testing or when rendering multiple independent pages in the
     * same process (e.g., server-side rendering scenarios).
     */
    public function reset(): void
    {
        $this->styles = [];
        $this->scopeMap = [];
    }

    /**
     * Gets all registered scope identifiers.
     *
     * Useful for debugging and testing.
     *
     * @return array<string> Array of scope identifiers (e.g., ['c-a3f8b9c4', 'c-def12345'])
     */
    public function getScopes(): array
    {
        return array_keys($this->styles);
    }

    /**
     * Gets the style rules for a specific scope identifier.
     *
     * Useful for debugging and testing.
     *
     * @param  string  $scopeId  The scope identifier
     * @return array|null The style rules, or null if not found
     */
    public function getStylesForScope(string $scopeId): ?array
    {
        return $this->styles[$scopeId] ?? null;
    }
}
