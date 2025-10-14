<?php

namespace IceTea\IceDOM;

use Closure;

use function array_is_list;
use function count;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function str_replace;

/**
 * HtmlNode - Represents an HTML element with attributes, children, and fluent API.
 *
 * Responsibility:
 * This class extends Node to provide HTML-specific functionality including tag names,
 * attributes (both normal and boolean), CSS class management, and proper HTML rendering.
 * It handles void elements (self-closing tags), attribute escaping, and provides a fluent
 * interface for building HTML elements programmatically with type safety.
 *
 * Key Features:
 * - HTML tag name and attribute management
 * - Boolean attribute handling (checked, disabled, etc.)
 * - Void element support (br, img, input, etc.)
 * - CSS class manipulation with conditional syntax
 * - Attribute methods via HtmlAttributeMethods trait
 * - Factory method for flexible instantiation
 * - HTML-safe attribute escaping
 *
 * Attribute Types:
 * 1. Boolean attributes: Presence-based (disabled, checked, selected, etc.)
 * 2. Value attributes: Key="value" format (id, class, href, etc.)
 * 3. Special '_' attribute: Raw unquoted content in opening tag
 * 4. Closure attributes: Evaluated dynamically during rendering
 *
 * Example:
 * ```php
 * $div = new HtmlNode(['Hello'], 'div', ['id' => 'main', 'class' => 'container']);
 * $div->setAttribute('data-value', 123);
 * echo $div; // <div id="main" class="container" data-value="123">Hello</div>
 *
 * $input = new HtmlNode([], 'input', ['type' => 'checkbox'], true);
 * $input->checked(true);
 * echo $input; // <input type="checkbox" checked>
 * ```
 *
 * @author IceTea Team
 *
 * @see Node Base class for tree structure and children
 * @see HtmlAttributeMethods Trait providing attribute setter methods
 */
class HtmlNode extends Node
{
    use HtmlAttributeMethods;

    /**
     * HTML boolean attributes that are rendered as presence-based.
     *
     * These attributes don't require values - their presence indicates true,
     * absence indicates false. Rendered as just the attribute name when true.
     *
     * @var array<string> List of boolean attribute names
     */
    public const BOOLEAN_ATTRS = ['allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'readonly', 'required', 'reversed', 'selected'];

    /**
     * HTML void elements that cannot contain children (self-closing tags).
     *
     * These elements don't have closing tags and throw exceptions if children
     * are added to them.
     *
     * @var array<string> List of void tag names
     */
    public const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    /**
     * The HTML tag name for this element.
     *
     * @var string Tag name (e.g., 'div', 'span', 'input')
     */
    protected string $tagName;

    /**
     * Associative array of HTML attributes for this element.
     *
     * Special keys:
     * - '_': Raw content in opening tag (unquoted)
     * - Other keys: Rendered as key="value" or key (for boolean attrs)
     *
     * @var array<string, mixed> Attribute name => value pairs
     */
    protected array $attrs = [];

    /**
     * Indicates if this is a void element (self-closing tag).
     *
     * @var bool True for void elements (no closing tag, no children allowed)
     */
    protected bool $isVoid = false;

    /**
     * Create a new HtmlNode instance with tag name, attributes, and children.
     *
     * Constructor validates tag name and initializes the HTML element structure.
     * Void elements cannot have children (enforced by appendChild override).
     *
     * @param  array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null>  $children  Child content for the element.
     *                                                                                                  Empty for void elements.
     * @param  string|null  $tagName  The HTML tag name (required).
     *                                - string: Tag name like 'div', 'span', 'input'
     *                                - null: Throws exception (tag name is mandatory)
     * @param  array<string, mixed>  $attrs  Initial HTML attributes.
     *                                       - Standard attributes: ['id' => 'main', 'class' => 'btn']
     *                                       - Boolean attributes: ['disabled' => true]
     *                                       - '_' key: Raw content in opening tag
     *                                       - Closure values: Evaluated during rendering
     * @param  bool  $isVoid  Whether this is a void/self-closing element.
     *                        - true: No closing tag, appendChild throws exception
     *                        - false: Normal element with closing tag
     *
     * @throws \Exception If tagName is null or empty string
     */
    public function __construct(
        array $children = [],
        ?string $tagName = null,
        array $attrs = [],
        bool $isVoid = false,
    ) {
        if (empty($tagName)) {
            throw new \Exception('Tag name is required');
        }

        parent::__construct($children);
        $this->tagName = $tagName;
        $this->attrs = $attrs;
        $this->isVoid = $isVoid;
    }

    /**
     * Factory method to create HTML elements with flexible argument patterns.
     *
     * Provides multiple ways to construct HTML elements based on the first argument type,
     * enabling concise syntax for common patterns. This is the recommended way to create
     * HTML elements when using the library's helper functions.
     *
     * Argument handling by type:
     * 1. String $firstArgument: Treated as '_' attribute (inner text), $children used for child nodes
     * 2. Associative array: Treated as attributes, $children used for child nodes
     * 3. List array: Treated as children (ignore $children parameter)
     * 4. null + array $children: Use $children as children
     * 5. Other types: Wrap $firstArgument as single child
     *
     * Examples:
     * ```php
     * // String as text content
     * HtmlNode::tag('div', 'Hello', []) // <div _="Hello"></div>
     *
     * // Attributes + children
     * HtmlNode::tag('div', ['class' => 'btn'], [$child]) // <div class="btn">$child</div>
     *
     * // List as children
     * HtmlNode::tag('ul', [$li1, $li2], null) // <ul>$li1$li2</ul>
     *
     * // Null + children
     * HtmlNode::tag('div', null, [$child]) // <div>$child</div>
     * ```
     *
     * @param  string  $tagName  The HTML tag name (div, span, etc.)
     * @param  mixed  $firstArgument  Flexible first parameter:
     *                                - string: Used as '_' attribute (text content)
     *                                - array: Attributes (associative) or children (list)
     *                                - null: Ignore, use $children parameter
     *                                - Other: Single child to wrap
     * @param  array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null>|null  $children  Child nodes when $firstArgument is string/attributes/null.
     *                                                                                                       - null: No children
     *                                                                                                       - array: Child nodes
     * @param  bool  $isVoid  Whether this is a void/self-closing element
     * @return static New HtmlNode instance with configured tag, attributes, and children
     */
    public static function tag(string $tagName, mixed $firstArgument, ?array $children, bool $isVoid = false): static
    {
        if (is_string($firstArgument)) {
            return new static(
                is_array($children) ? $children : [],
                $tagName,
                ['_' => $firstArgument],
                $isVoid
            );
        }

        if (is_array($firstArgument)) {
            if (array_is_list($firstArgument)) {
                return new static($firstArgument, $tagName, [], $isVoid);
            }

            if (isset($firstArgument[0]) && ! isset($firstArgument['_'])) {
                $firstArgument['_'] = $firstArgument[0];
                unset($firstArgument[0]);
            }

            return new static(
                is_array($children) ? $children : [],
                $tagName,
                $firstArgument,
                $isVoid
            );
        }

        if ($firstArgument === null && is_array($children)) {
            return new static($children, $tagName, [], $isVoid);
        }

        return new static([$firstArgument], $tagName, [], $isVoid);
    }

    /**
     * Adds a single child to this HTML element.
     *
     * Overrides parent appendChild() to enforce void element restrictions.
     * Void elements (br, img, input, etc.) cannot have children and will throw
     * an exception if you attempt to add children to them.
     *
     * @param  Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null  $child  The child content to add.
     *                                                                                        See Node::appendChild() for type details.
     * @return static Returns $this for method chaining
     *
     * @throws \Exception If attempting to add children to a void element
     */
    public function appendChild($child): static
    {
        if ($this->isVoid) {
            throw new \Exception("Void element <{$this->tagName}> cannot have children.");
        }

        return parent::appendChild($child);
    }

    /**
     * Converts attributes array to HTML attribute string for rendering.
     *
     * Processes each attribute according to its type and name, applying proper
     * escaping and formatting. Called internally by __toString() to generate
     * the attributes portion of the opening tag.
     *
     * Attribute processing rules:
     * 1. '_' key: Output as raw unquoted content (e.g., attr="_value" => " _value")
     * 2. Closure values: Evaluated with this node as parameter first
     * 3. Boolean attrs with false: Skipped (not rendered)
     * 4. Boolean attrs with true/truthy: Rendered as just attribute name
     * 5. Other values: Rendered as key="escapedValue"
     * 6. null values: Skipped (not rendered)
     *
     * Escaping:
     * - Both keys and values are escaped using htmlspecialchars()
     * - Uses ENT_FLAGS (ENT_QUOTES | ENT_HTML5)
     *
     * Output format:
     * - Each attribute prefixed with space
     * - Boolean: " attrname"
     * - Value: " key=\"value\""
     * - Special: " rawcontent"
     *
     * @return string Space-prefixed attribute string, empty if no attributes
     */
    protected function attributesToString(): string
    {
        if (count($this->attrs) == 0) {
            return '';
        }

        $attrStrs = [];
        foreach ($this->attrs as $key => $value) {
            if ($key == '_') {
                $attrStrs[] = " {$value}";

                continue;
            }

            if ($value instanceof Closure) {
                $value = $value($this);
            }

            if ($value === false && in_array($key, self::BOOLEAN_ATTRS)) {
                continue;
            }

            if (
                $value === true ||
                (in_array($key, self::BOOLEAN_ATTRS) && (bool) $value)
            ) {
                $attrStrs[] = " {$key}";
            } elseif ($value !== null) {
                $key = htmlspecialchars($key, static::ENT_FLAGS);
                $value = htmlspecialchars((string) $value, static::ENT_FLAGS);
                $attrStrs[] = " {$key}=\"{$value}\"";
            }
        }

        return implode('', $attrStrs);
    }

    /**
     * Sets an HTML attribute value.
     *
     * @param  string  $key  The attribute name (e.g., 'id', 'class', 'data-value')
     * @param  mixed  $value  The attribute value.
     *                        - string/int/float: Rendered as key="value"
     *                        - bool: For boolean attrs, true renders as just key, false skips
     *                        - Closure: Evaluated during rendering
     *                        - null: Attribute not rendered
     * @return static Returns $this for method chaining
     */
    public function setAttribute($key, $value): static
    {
        $this->attrs[$key] = $value;

        return $this;
    }

    /**
     * Gets an HTML attribute value.
     *
     * @param  string  $key  The attribute name to retrieve
     * @param  mixed  $default  Default value returned if attribute doesn't exist
     * @return mixed The attribute value if set, otherwise $default
     */
    public function getAttribute($key, $default = null)
    {
        return $this->attrs[$key] ?? $default;
    }

    /**
     * Magic method for dynamic attribute setting via method calls.
     *
     * Allows setting attributes using method syntax instead of setAttribute().
     * Automatically converts underscores to hyphens for hyphenated HTML attributes.
     *
     * Example:
     * ```php
     * $node->data_value('123'); // Sets data-value="123"
     * $node->aria_label('Button'); // Sets aria-label="Button"
     * ```
     *
     * @param  string  $key  The method name (becomes attribute name, underscores converted to hyphens)
     * @param  array<mixed>  $args  Method arguments, first element becomes attribute value
     * @return static Returns $this for method chaining
     */
    public function __call($key, $args): static
    {
        $key = str_replace('_', '-', $key);

        return $this->setAttribute($key, $args[0] ?? null);
    }

    /**
     * Magic method for dynamic attribute getting via property access.
     *
     * Allows reading attributes using property syntax instead of getAttribute().
     * Automatically converts underscores to hyphens for hyphenated HTML attributes.
     *
     * Example:
     * ```php
     * $value = $node->data_value; // Gets data-value attribute
     * $label = $node->aria_label; // Gets aria-label attribute
     * ```
     *
     * @param  string  $key  The property name (becomes attribute name, underscores converted to hyphens)
     * @return mixed The attribute value or null if not set
     */
    public function __get($key)
    {
        $key = str_replace('_', '-', $key);

        return $this->getAttribute($key);
    }

    /**
     * Sets the HTML id attribute.
     *
     * Convenience method for setting the id attribute (equivalent to setAttribute('id', $id)).
     *
     * @param  string  $id  The unique identifier for this element
     * @return static Returns $this for method chaining
     */
    public function id($id): static
    {
        return $this->setAttribute('id', $id);
    }

    /**
     * Sets CSS classes with flexible syntax supporting conditionals.
     *
     * Accepts multiple patterns for specifying classes:
     * - Simple strings: Added as class names
     * - List arrays: All elements added as class names
     * - Associative arrays: Keys are class names, values are conditions (boolean)
     * - Multiple arguments: Combined into single class string
     *
     * The method merges all patterns and generates a space-separated class attribute
     * containing only the classes whose conditions evaluated to true.
     *
     * Examples:
     * ```php
     * $node->classes('btn', 'btn-primary'); // class="btn btn-primary"
     * $node->classes(['active' => $isActive, 'disabled' => false]); // class="active" (if $isActive true)
     * $node->classes(['btn', 'primary'], 'large'); // class="btn primary large"
     * ```
     *
     * @param  array<string|bool>|string|null  ...$args  Variable class specifications.
     *                                                   - string: Class name to add
     *                                                   - array (list): ['class1', 'class2'] all added
     *                                                   - array (assoc): ['class' => true/false] conditional
     *                                                   - null: Ignored
     * @return static Returns $this for method chaining
     */
    public function classes(array|string|null ...$args): static
    {
        if (empty($args)) {
            return $this;
        }

        $classes = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $classes[$arg] = true;
            } elseif (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $classes[$value] = true;
                    } elseif (is_string($key)) {
                        $classes[$key] = (bool) $value;
                    }
                }
            }
        }

        $class = implode(' ', array_keys(array_filter($classes)));

        return $this->setAttribute('class', $class);
    }

    /**
     * Converts the HTML node to its complete HTML string representation.
     *
     * Generates proper HTML markup based on whether this is a void or normal element.
     * Called automatically when the node is used in string context.
     *
     * Rendering logic:
     * - Void elements: <tagName attributes> (no closing tag, no children)
     * - Normal elements: <tagName attributes>children</tagName>
     *
     * Example outputs:
     * ```php
     * // Void: <input type="text" disabled>
     * // Normal: <div id="main" class="container">Hello</div>
     * ```
     *
     * @return string Complete HTML markup for this element
     */
    public function __toString()
    {
        if ($this->isVoid) {
            return "<{$this->tagName}{$this->attributesToString()}>";
        }

        return "<{$this->tagName}{$this->attributesToString()}>{$this->childrenToString()}</{$this->tagName}>";
    }

    /**
     * Converts the HTML node to an array representation for serialization.
     *
     * Produces a structured array containing all node properties with closures
     * evaluated and child HtmlNodes recursively converted. Useful for JSON
     * serialization, debugging, or state inspection.
     *
     * Array structure:
     * - 'tagName': string - The HTML tag name
     * - 'attrs': array - Attributes with Closures evaluated
     * - 'children': array - Child nodes (HtmlNodes converted to arrays, others kept as-is)
     * - 'isVoid': bool - Whether this is a void element
     *
     * Example:
     * ```php
     * $div = div(['class' => 'btn'], ['Click me']);
     * $array = $div->toArray();
     * // ['tagName' => 'div', 'attrs' => ['class' => 'btn'], 'children' => ['Click me'], 'isVoid' => false]
     * ```
     *
     * @return array<string, mixed> Array representation with evaluated closures and recursive child conversion
     */
    public function toArray()
    {
        $closureFunction = function ($value) {
            return $value instanceof Closure ? $value($this) : $value;
        };
        $closureChildFunction = function ($value) {
            if ($value instanceof HtmlNode) {
                return $value->toArray();
            }

            return $value;
        };

        return [
            'children' => array_map($closureChildFunction, $this->children),
            'tagName' => $this->tagName,
            'attrs' => array_map($closureFunction, $this->attrs),
            'isVoid' => $this->isVoid,
        ];
    }
}
