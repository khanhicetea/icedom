<?php

namespace IceTea\IceDOM;

use Closure;
use Stringable;

use function count;
use function htmlspecialchars;
use function implode;

/**
 * Node - Base class for building DOM tree structures with fluent API.
 *
 * Responsibility:
 * This class serves as the fundamental building block for creating tree-structured
 * DOM representations. It manages parent-child relationships, handles various content
 * types (nodes, strings, closures, numbers), and provides HTML escaping for security.
 * The class implements a fluent API pattern for chainable method calls and supports
 * functional programming patterns through closure evaluation and hook mechanisms.
 *
 * Core Features:
 * - Tree structure management with parent-child relationships
 * - Mixed content type support (Node, string, int, float, Closure, Stringable, SafeStringable)
 * - Automatic HTML escaping for security (except SafeStringable and numeric types)
 * - Lazy evaluation through Closure support
 * - Fluent/chainable API for building complex structures
 * - Functional programming utilities (use, childrenUse, map)
 *
 * Content Type Handling:
 * - Node: Rendered recursively with parent relationship maintained
 * - string: HTML-escaped for XSS protection
 * - int/float: Output directly without escaping
 * - Closure: Evaluated lazily with parent node as parameter
 * - SafeStringable: Output without escaping (for pre-sanitized content)
 * - Stringable: Converted to string then HTML-escaped
 * - null: Ignored and not rendered
 *
 * @author IceTea Team
 */
class Node
{
    /**
     * HTML entity encoding flags for secure output.
     *
     * Combines ENT_QUOTES (encode both double and single quotes) with ENT_HTML5
     * (HTML5-compliant entity handling) to ensure safe HTML output.
     *
     * @var int Bitwise combination of ENT_QUOTES | ENT_HTML5
     */
    public const ENT_FLAGS = ENT_QUOTES | ENT_HTML5;

    /**
     * Reference to the parent node in the DOM tree hierarchy.
     *
     * @var Node|null The parent node, or null if this is a root node without parent
     */
    protected ?Node $parent = null;

    /**
     * Collection of child nodes and content to be rendered within this node.
     *
     * Type handling during rendering:
     * - Node: Recursively rendered with __toString(), parent relationship set
     * - Closure: Evaluated with parent as parameter, result processed
     * - string: HTML-escaped using htmlspecialchars() for XSS protection
     * - int|float: Cast to string without escaping
     * - SafeStringable: Output directly without escaping (trusted content)
     * - Stringable: Converted via __toString() then HTML-escaped
     * - null: Skipped during rendering
     *
     * Order: Children maintain insertion order and are rendered sequentially.
     * Spacing: Children are concatenated without separators (no spaces).
     *
     * @var array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null>
     */
    protected array $children = [];

    /**
     * Creates a new Node instance with optional initial children.
     *
     * All provided children are added via appendChildren(), which handles
     * parent relationship setting for Node instances.
     *
     * @param  array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null>  $children  Initial child content.
     *                                                                                                  - Node: Child nodes with parent set automatically
     *                                                                                                  - Closure: Lazy-evaluated content
     *                                                                                                  - string/int/float: Direct content
     *                                                                                                  - SafeStringable: Pre-sanitized HTML content
     *                                                                                                  - Stringable: Objects with __toString()
     *                                                                                                  - ArrayMap: Iterable mapping utility
     *                                                                                                  - null: Ignored
     */
    public function __construct(
        array $children = [],
    ) {
        $this->appendChildren($children);
    }

    /**
     * Sets the parent node for establishing tree hierarchy.
     *
     * This method is called internally when appendChild() adds a Node child.
     * It creates the upward link in the parent-child relationship, allowing
     * nodes to access their parent context.
     *
     * @param  Node|null  $parent  The parent node reference.
     *                             - Node: Establishes this node as a child of the parent
     *                             - null: Detaches this node from its parent (orphan node)
     * @return void
     */
    public function setParent(?Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Gets the current parent node reference.
     *
     * @return Node|null The parent node if this node is a child, null if root or orphaned
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * Applies a hook function to this node for configuration or transformation.
     *
     * Enables functional programming patterns by executing a closure with this node
     * as both the execution context ($this) and the first parameter. Useful for
     * applying conditional logic, plugins, or reusable configuration functions.
     *
     * Example:
     * ```php
     * $node->use(function($n) {
     *     $n->setAttribute('class', 'container');
     *     return $n;
     * });
     * ```
     *
     * @param  Closure|callable|null  $hook  The transformation function to apply.
     *                                       - Closure: Executed with call($this, $this)
     *                                       - callable: Converted to Closure then executed
     *                                       - null: Ignored, no operation performed
     *                                       Signature: function(Node $node): void|mixed
     * @return static Returns $this for method chaining regardless of hook return value
     */
    public function use(Closure|callable|null $hook): static
    {
        $hook = is_callable($hook) ? Closure::fromCallable($hook) : $hook;

        if ($hook instanceof Closure) {
            $hook->call($this, $this);
        }

        return $this;
    }

    /**
     * Applies a hook function to all Node children in batch.
     *
     * Iterates through all children, applying the hook to each Node instance
     * (non-Node children are skipped). The hook is executed via the child's use()
     * method, so the hook receives the child node as both context and parameter.
     *
     * Use cases:
     * - Batch attribute setting on all child nodes
     * - Applying consistent styling or classes
     * - Indexing or numbering child elements
     *
     * Example:
     * ```php
     * $node->childrenUse(function($child) {
     *     $child->setAttribute('data-processed', 'true');
     * });
     * ```
     *
     * @param  Closure|callable|null  $hook  The function to apply to each Node child.
     *                                       - Closure: Applied to each Node child
     *                                       - callable: Converted to Closure then applied
     *                                       - null: Ignored, no operation
     *                                       Signature: function(Node $child): void|mixed
     *                                       Note: Non-Node children (strings, numbers, etc.) are skipped
     * @return static Returns $this for method chaining
     */
    public function childrenUse(Closure|callable|null $hook): static
    {
        $hook = is_callable($hook) ? Closure::fromCallable($hook) : $hook;

        foreach ($this->children as $child) {
            if ($child instanceof Node) {
                $child->use($hook);
            }
        }

        return $this;
    }

    /**
     * Removes all children from this node, resulting in empty content.
     *
     * Clears the internal children array completely. Note that removed Node children
     * retain their parent reference until they are added elsewhere or garbage collected.
     *
     * @return static Returns $this for method chaining
     */
    public function clearChildren(): static
    {
        $this->children = [];

        return $this;
    }

    /**
     * Adds a single child element to this node.
     *
     * Accepts multiple content types, each handled differently during rendering:
     * - Node instances get their parent reference set immediately
     * - null values are ignored (no-op)
     * - All other types are stored and processed during __toString()
     *
     * @param  Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null  $child  The content to add.
     *                                                                                        - Node: Another DOM node, parent set automatically
     *                                                                                        - Closure: Lazy-evaluated function, called with parent during render
     *                                                                                        - string: Text content, HTML-escaped during render
     *                                                                                        - int|float: Numeric content, not escaped
     *                                                                                        - SafeStringable: Pre-sanitized HTML, not escaped
     *                                                                                        - Stringable: Object with __toString(), escaped during render
     *                                                                                        - ArrayMap: Iterable mapper, rendered during __toString()
     *                                                                                        - null: Ignored, no action taken
     * @return static Returns $this for method chaining
     */
    public function appendChild($child): static
    {
        if ($child === null) {
            return $this;
        }
        if ($child instanceof Node) {
            $child->setParent($this);
        }
        $this->children[] = $child;

        return $this;
    }

    /**
     * Adds multiple children to this node in batch.
     *
     * Convenience method that iterates the array and calls appendChild()
     * for each element, ensuring consistent handling of all content types.
     *
     * @param  array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null>  $children  Array of content to add.
     *                                                                                                  Each element handled according to appendChild() rules.
     *                                                                                                  See appendChild() documentation for type-specific behavior.
     * @return static Returns $this for method chaining
     */
    public function appendChildren(array $children): static
    {
        foreach ($children as $child) {
            $this->appendChild($child);
        }

        return $this;
    }

    /**
     * Creates and appends an ArrayMap for rendering iterable data collections.
     *
     * Convenience method that creates an ArrayMap instance, sets this node as its
     * parent context, and appends it as a child. The ArrayMap will render during
     * __toString() by mapping over the iterable and applying the transformation.
     *
     * Use cases:
     * - Rendering lists from database results
     * - Building repetitive HTML structures
     * - Dynamic list generation
     *
     * @param  iterable<mixed>  $arr  The collection to iterate and render.
     *                                - array: Standard PHP array
     *                                - Iterator/Generator: Any iterable object
     * @param  callable|null  $mapFunction  Optional transformation for each element.
     *                                      - callable: Function receiving ($value, $key), returning renderable content
     *                                      - null: Elements used directly without transformation
     *                                      Signature: function(mixed $value, int|string $key): Node|string|Stringable|null
     * @return static Returns $this for method chaining
     */
    public function map(iterable $arr, $mapFunction = null): static
    {
        $arrayMap = new ArrayMap($arr, $mapFunction);
        $arrayMap->setParent($this);

        return $this->appendChild($arrayMap);
    }

    /**
     * Magic method enabling function-call syntax for adding children.
     *
     * Provides syntactic sugar for a more concise, natural way to build DOM trees.
     * Internally delegates to appendChildren().
     *
     * Example:
     * ```php
     * $div = new Node();
     * $div('Hello', $span, 'World');
     * // Equivalent to: $div->appendChildren(['Hello', $span, 'World']);
     * ```
     *
     * @param  mixed  ...$children  Variable number of children to append.
     *                              Each child can be any type accepted by appendChild().
     * @return static Returns $this for method chaining
     */
    public function __invoke(...$children): static
    {
        return $this->appendChildren($children);
    }

    /**
     * Gets all children of this node.
     *
     * Returns the internal children array. Note: The returned array is a reference
     * in PHP 7+, but modifying it directly is not recommended. Use appendChild()
     * and clearChildren() for proper manipulation.
     *
     * @return array<Node|Closure|string|int|float|SafeStringable|Stringable|ArrayMap|null> Array of all child content
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Converts all children to concatenated string output.
     *
     * Core rendering method that processes each child according to its type,
     * applying appropriate escaping and evaluation. This method is called by
     * __toString() to generate the final output.
     *
     * Processing rules by type:
     * 1. Closure: Evaluated first with parent node as parameter, then result processed
     * 2. Node|SafeStringable: __toString() called, output not escaped
     * 3. string: HTML-escaped using htmlspecialchars()
     * 4. int|float: Cast to string without escaping
     * 5. Stringable: __toString() called, then HTML-escaped
     * 6. null: Skipped, no output
     *
     * Security: All string content is escaped except SafeStringable and numeric types.
     * Concatenation: Children joined without separators (empty string join).
     *
     * @return string The rendered content of all children, empty string if no children
     */
    protected function childrenToString(): string
    {
        if (count($this->children) == 0) {
            return '';
        }

        $strArr = [];
        foreach ($this->children as $child) {
            if ($child instanceof Closure) {
                $child = $child($this);
            }

            if ($child instanceof Node || $child instanceof SafeStringable) {
                $strArr[] = $child->__toString();
            } elseif (is_string($child)) {
                $strArr[] = htmlspecialchars($child, static::ENT_FLAGS);
            } elseif ($child instanceof Stringable) {
                $strArr[] = htmlspecialchars($child->__toString(), static::ENT_FLAGS);
            } elseif (is_int($child) || is_float($child)) {
                $strArr[] = (string) $child;
            }
        }

        return implode('', $strArr);
    }

    /**
     * Evaluates closures or returns values as-is.
     *
     * Utility method for consistent lazy evaluation handling across the class.
     * Used internally for conditional evaluation of potentially lazy values.
     *
     * @param  mixed  $value  The value to potentially evaluate.
     *                        - Closure: Executed with $this as parameter, returns result
     *                        - Other types: Returned unchanged
     * @return mixed The closure result if Closure, otherwise the original value
     */
    protected function tryEvalClosure($value)
    {
        if ($value instanceof Closure) {
            return $value($this);
        }

        return $value;
    }

    /**
     * Converts the node to its string representation.
     *
     * Magic method called when the node is used in string context. Delegates
     * to childrenToString() which handles type-specific rendering and escaping.
     *
     * @return string The rendered HTML/text content of this node and all its children
     */
    public function __toString()
    {
        return $this->childrenToString();
    }
}
