<?php

namespace IceTea\IceDOM;

use Closure;
use Stringable;

use function count;
use function htmlspecialchars;
use function implode;

/**
 * Abstract base class for DOM nodes in the IceDOM library.
 *
 * This class provides the foundation for building HTML structures programmatically
 * using a fluent, chainable API. It implements a tree structure where nodes can
 * contain children and maintain parent-child relationships.
 *
 * Nodes support mixed content types including strings, numbers, other nodes,
 * closures for lazy evaluation, and any Stringable objects. The class handles
 * HTML escaping for security and provides functional programming utilities.
 */
abstract class Node
{
    /**
     * HTML entity encoding flags for secure output.
     *
     * Uses ENT_QUOTES to encode both double and single quotes, and ENT_HTML5
     * for HTML5-compliant entity handling.
     */
    public const ENT_FLAGS = ENT_QUOTES | ENT_HTML5;

    /**
     * Reference to the parent node in the DOM tree.
     *
     * @var Node|null The parent node, or null if this is a root node
     */
    protected ?Node $parent = null;

    /**
     * Array of child nodes and content that will be rendered within this node.
     *
     * This array can contain mixed content types:
     * - Node instances: Child DOM nodes that are recursively rendered
     * - Closure: Lazy-evaluated content that receives the parent node as argument
     * - string: Text content that will be HTML-escaped for security
     * - int|float: Numeric values that are output directly without escaping
     * - SafeString: Objects implementing safe string interface that bypass HTML escaping
     * - Stringable: Any object with __toString() method that will be HTML-escaped
     *
     * Children maintain insertion order and are processed sequentially during rendering.
     * Each child automatically has its parent reference set when added.
     *
     * @var array<(Node|Closure|string|int|float|SafeString|Stringable|null)>
     */
    protected array $children = [];

    /**
     * Creates a new Node instance with optional initial children.
     *
     * @param  array<(Node|Closure|string|int|float|SafeString|Stringable|null)>  $children
     *                                                                                       Initial children to add to this node
     */
    public function __construct(
        array $children = [],
    ) {
        $this->appendChildren($children);
    }

    /**
     * Sets the parent node for this node.
     *
     * This method is typically called internally when a node is added
     * as a child to another node. It establishes the parent-child relationship
     * in the DOM tree.
     *
     * @param  Node  $parent  The parent node to set
     * @return void
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Gets the parent node of this node.
     *
     * @return Node|null The parent node, or null if this is a root node
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * Applies a closure hook to this node for configuration or modification.
     *
     * This method enables functional programming patterns by allowing you to
     * apply transformations or configurations to the current node. The closure
     * receives the node as both the context ($this) and as the first parameter.
     *
     * Example:
     * ```php
     * $node->use(function($n) {
     *     $n->setAttribute('class', 'container');
     * });
     * ```
     *
     * @param  Closure|callable|null  $hook  A closure or callable that will be executed
     *                                       with this node as context and first parameter. Null values are ignored.
     * @return static Returns $this for method chaining
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
     * Applies a closure hook to all Node children of this node.
     *
     * This method iterates through all Node children and applies the given closure
     * to each one. The closure receives the parent node as context and the child
     * node as the first parameter. This is useful for batch operations on children.
     *
     * Example:
     * ```php
     * $node->childrenUse(function($parent, $child) {
     *     $child->setAttribute('data-index', $parent->indexOf($child));
     * });
     * ```
     *
     * @param  Closure|callable|null  $hook  A closure or callable that will be executed
     *                                       for each child, with parent as context and child as first parameter.
     *                                       Null values are ignored.
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
     * Removes all children from this node.
     *
     * This method clears the children array, effectively removing all child nodes
     * and content from this node. The removed children lose their parent reference.
     *
     * @return static Returns $this for method chaining
     */
    public function clearChildren(): static
    {
        $this->children = [];

        return $this;
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
     * Adds multiple children to this node.
     *
     * This method iterates through the provided array and adds each child
     * using appendChild(), ensuring proper handling of all content types.
     *
     * @param  array<(Node|Closure|string|int|float|SafeString|Stringable|null)>  $children
     *                                                                                       Array of children to add
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
     * Creates and adds an ArrayMapNode for rendering arrays of data.
     *
     * This method provides a convenient way to render collections of data
     * by creating an ArrayMapNode that will iterate over the provided array
     * and apply the mapping function to each element.
     *
     * @param  iterable  $arr  The array or iterable to map over
     * @param  callable|null  $mapFunction  Optional function to transform each element
     * @return static Returns $this for method chaining
     */
    public function map(iterable $arr, $mapFunction = null): static
    {
        return $this->appendChild(new ArrayMapNode([], $arr, $mapFunction));
    }

    /**
     * Magic method to allow calling the node like a function to add children.
     *
     * This enables a more concise syntax for adding children:
     * ```php
     * $node($child1, $child2, $child3);
     * // Equivalent to:
     * $node->appendChildren([$child1, $child2, $child3]);
     * ```
     *
     * @param  mixed  ...$children  Variable number of children to add
     * @return static Returns $this for method chaining
     */
    public function __invoke(...$children): static
    {
        return $this->appendChildren($children);
    }

    /**
     * Gets all children of this node.
     *
     * Returns a copy of the internal children array. Modifying the returned
     * array will not affect the node's actual children.
     *
     * @return array<(Node|Closure|string|int|float|SafeString|Stringable|null)>
     *                                                                           Array of all children
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Converts all children to their string representation.
     *
     * This method processes each child according to its type:
     * - Closures are evaluated with the current node as parameter
     * - Nodes are recursively converted to string and have parent set
     * - Strings are HTML-escaped for security
     * - Numbers and SafeString objects are output directly
     * - Other Stringable objects are HTML-escaped
     *
     * The resulting strings are joined with spaces between them.
     *
     * @return string The concatenated string representation of all children
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

            if ($child instanceof Node || $child instanceof SafeString) {
                $strArr[] = $child->__toString();
            } elseif (is_string($child)) {
                $strArr[] = htmlspecialchars($child, static::ENT_FLAGS);
            } elseif ($child instanceof Stringable) {
                $strArr[] = htmlspecialchars($child->__toString(), static::ENT_FLAGS);
            } elseif (is_int($child) || is_float($child)) {
                $strArr[] = (string) $child;
            }
        }

        return implode(' ', $strArr);
    }

    /**
     * Evaluates a closure if the value is a Closure, otherwise returns the value.
     *
     * This utility method provides a consistent way to handle potentially
     * lazy-evaluated values throughout the class.
     *
     * @param  mixed  $value  The value to evaluate
     * @return mixed The evaluated result or original value
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
     * @return string The string representation of this node
     */
    public function __toString()
    {
        return $this->childrenToString();
    }
}
