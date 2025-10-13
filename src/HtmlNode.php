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
 * Represents an HTML element node with attributes and children.
 *
 * This class provides a fluent interface for building HTML elements programmatically,
 * supporting attribute manipulation, class management, and HTML generation.
 */
class HtmlNode extends Node
{
    use HtmlAttributeMethods;

    /**
     * List of HTML attributes that are boolean (presence-based rather than value-based).
     *
     * @var array<string>
     */
    public const BOOLEAN_ATTRS = ['allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'readonly', 'required', 'reversed', 'selected'];

    /**
     * List of HTML void elements that cannot have children.
     *
     * @var array<string>
     */
    public const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    /**
     * The HTML tag name for this element.
     */
    protected string $tagName;

    /**
     * Array of HTML attributes for this element.
     *
     * @var array<string, mixed>
     */
    protected array $attrs = [];

    /**
     * Whether this is a void element (cannot have children).
     */
    protected bool $isVoid = false;

    /**
     * Create a new HtmlNode instance.
     *
     * @param  array<Node|scalar>  $children  Array of child nodes or scalar values
     * @param  string|null  $tagName  The HTML tag name
     * @param  array<string, mixed>  $attrs  Array of HTML attributes
     * @param  bool  $isVoid  Whether this is a void element
     */
    public function __construct(
        array $children = [],
        ?string $tagName = null,
        array $attrs = [],
        bool $isVoid = false,
    ) {
        parent::__construct($children);
        $this->tagName = $tagName;
        $this->attrs = $attrs;
        $this->isVoid = $isVoid;
    }

    /**
     * Factory method to create an HTML element with flexible argument handling.
     *
     * This method provides a convenient way to create HTML elements with various
     * argument combinations:
     * - If $firstArgument is string: treated as inner text content
     * - If $firstArgument is array: treated as attributes (if associative) or children (if list)
     * - If $firstArgument is null: uses $children as the children array
     * - Otherwise: $firstArgument is wrapped as a single child
     *
     * @param  string  $tagName  The HTML tag name
     * @param  mixed  $firstArgument  Either attributes array, content string, or child node
     * @param  array<Node|scalar>|null  $children  Array of child nodes or scalar values
     * @param  bool  $isVoid  Whether this is a void element
     * @return static New HtmlNode instance
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
     * Adds a single child to this node.
     *
     * This method accepts various content types and handles them appropriately.
     * Node instances will have their parent reference set automatically.
     * Null values are ignored and do not affect the node.
     *
     * @param  Node|Closure|string|int|float|SafeString|Stringable|null  $child
     *                                                                           The child content to add
     * @return static Returns $this for method chaining
     */
    public function appendChild($child): static
    {
        if ($this->isVoid) {
            throw new \Exception("Void element <{$this->tagName}> cannot have children.");
        }

        return parent::appendChild($child);
    }

    /**
     * Convert the attributes array to an HTML attribute string.
     *
     * Handles various attribute types:
     * - Boolean attributes: presence-based (disabled, checked, etc.)
     * - String attributes: key="value" format with proper escaping
     * - Closure attributes: evaluated with current node as context
     * - Special '_' attribute: raw text content (for class shorthand, etc.)
     *
     * @return string HTML attributes string (empty string if no attributes)
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
     * Set an HTML attribute value.
     *
     * @param  string  $key  The attribute name
     * @param  mixed  $value  The attribute value
     * @return static Returns self for method chaining
     */
    public function setAttribute($key, $value): static
    {
        $this->attrs[$key] = $value;

        return $this;
    }

    /**
     * Get an HTML attribute value.
     *
     * @param  string  $key  The attribute name
     * @param  mixed  $default  Default value if attribute doesn't exist
     * @return mixed The attribute value or default
     */
    public function getAttribute($key, $default = null)
    {
        return $this->attrs[$key] ?? $default;
    }

    /**
     * Magic method to handle dynamic attribute setting.
     *
     * Allows setting attributes using method calls like $node->href('#link')
     * Converts underscores to hyphens for HTML attribute compatibility.
     *
     * @param  string  $key  The method name (attribute name with underscores)
     * @param  array  $args  Method arguments (first argument becomes attribute value)
     * @return static Returns self for method chaining
     */
    public function __call($key, $args): static
    {
        $key = str_replace('_', '-', $key);

        return $this->setAttribute($key, $args[0] ?? null);
    }

    /**
     * Magic method to handle dynamic attribute getting.
     *
     * Allows getting attributes using property access like $node->href
     * Converts underscores to hyphens for HTML attribute compatibility.
     *
     * @param  string  $key  The property name (attribute name with underscores)
     * @return mixed The attribute value or null if not set
     */
    public function __get($key)
    {
        $key = str_replace('_', '-', $key);

        return $this->getAttribute($key);
    }

    /**
     * Set the HTML id attribute.
     *
     * @param  string  $id  The id value
     * @return static Returns self for method chaining
     */
    public function id($id): static
    {
        return $this->setAttribute('id', $id);
    }

    /**
     * Set CSS classes with flexible argument handling.
     *
     * Accepts multiple argument types:
     * - String: class name to add
     * - Array: associative [class => condition] or indexed list of class names
     * - Multiple arguments: combines all provided classes
     *
     * Examples:
     * $node->classes('active', 'large')
     * $node->classes(['active' => $isActive, 'large' => true])
     * $node->classes(['primary', 'secondary'])
     *
     * @param  array<string|bool>|string|null  ...$args  Class names or conditional arrays
     * @return static Returns self for method chaining
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
     * Convert the HTML node to its string representation.
     *
     * Generates the complete HTML markup for this element, including
     * opening tag, attributes, children, and closing tag (unless void element).
     *
     * @return string The complete HTML markup
     */
    public function __toString()
    {
        if ($this->isVoid) {
            return "<{$this->tagName}{$this->attributesToString()}>";
        }

        return "<{$this->tagName}{$this->attributesToString()}>{$this->childrenToString()}</{$this->tagName}>";
    }

    /**
     * Convert the HTML node to an array representation.
     *
     * Returns a structured array containing the node's properties,
     * with closures evaluated and child nodes recursively converted.
     *
     * @return array<string, mixed> Array representation with keys:
     *                              - 'children': array of child nodes (converted to arrays if HtmlNode)
     *                              - 'tagName': the HTML tag name
     *                              - 'attrs': array of attributes (closures evaluated)
     *                              - 'isVoid': boolean indicating if this is a void element
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
