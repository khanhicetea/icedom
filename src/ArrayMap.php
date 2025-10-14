<?php

namespace IceTea\IceDOM;

use Closure;
use Stringable;

use function is_callable;

/**
 * ArrayMap - Maps iterable collections to string output with optional transformation.
 *
 * Responsibility:
 * This class is responsible for transforming iterable data structures (arrays, iterators)
 * into string representations by applying an optional mapping function to each element.
 * It acts as a bridge between data collections and DOM rendering, allowing dynamic
 * list rendering within the DOM tree. The class maintains parent-child relationships
 * for proper DOM tree structure when generating Node instances.
 *
 * Use Cases:
 * - Rendering lists of data items as HTML elements
 * - Transforming database results into DOM nodes
 * - Dynamic content generation from arrays
 * - Building repetitive HTML structures programmatically
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 */
class ArrayMap implements Stringable
{
    /**
     * The iterable data source to be mapped and rendered.
     *
     * @var iterable<mixed>|null Array, Iterator, or any iterable structure; null means empty output
     */
    protected ?iterable $arr;

    /**
     * The transformation function applied to each array element.
     *
     * @var callable|null Function signature: function(mixed $value, int|string $key): Node|string|Stringable|null
     */
    protected $mapFunction;

    /**
     * Reference to the parent node in the DOM tree.
     *
     * This context is used to bind the mapping function and set parent relationships
     * for any Node instances created during mapping.
     *
     * @var Node|null The parent node context, or null if not attached to a parent
     */
    protected ?Node $parent = null;

    /**
     * Create a new ArrayMap instance.
     *
     * @param iterable<mixed>|null $arr The iterable data source to map over.
     *                                   - array: Standard PHP array
     *                                   - Iterator: Any object implementing Iterator interface
     *                                   - Generator: Result from a generator function
     *                                   - null: Produces empty string output
     * @param callable|null $mapFunction Optional transformation function for each element.
     *                                   - Receives: ($value, $key) where $value is the current element and $key is its index/key
     *                                   - Returns: Node|string|Stringable|null to be rendered
     *                                   - null: Elements are used directly without transformation
     *                                   - Bound to parent node context when rendering if parent is set
     */
    public function __construct(
        ?iterable $arr = null,
        $mapFunction = null,
    ) {
        $this->arr = $arr;
        $this->mapFunction = $mapFunction;
    }

    /**
     * Set the parent node context for this ArrayMap.
     *
     * Establishes the parent-child relationship in the DOM tree. When set, the mapping
     * function will be bound to the parent node context, allowing it to access parent
     * properties and methods. Any Node instances created during mapping will have their
     * parent reference set to this parent node.
     *
     * @param Node|null $parent The parent node to establish context with.
     *                          - Node: Sets the parent context for binding and relationships
     *                          - null: Removes parent context
     * @return static Returns $this for method chaining
     */
    public function setParent(?Node $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the current parent node context.
     *
     * @return Node|null The parent node if set, or null if no parent context exists
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * Convert the ArrayMap to its string representation.
     *
     * Processing steps:
     * 1. Returns empty string if array source is null
     * 2. Creates a shadow Node to collect mapped items
     * 3. Binds mapping function to parent context if both exist
     * 4. Iterates over array, applying mapping function to each element
     * 5. Appends each result to shadow node, setting parent for Node instances
     * 6. Returns concatenated string output of all items
     *
     * Type handling:
     * - Unmapped elements are used as-is
     * - Mapped elements can be Node, string, Stringable, or null
     * - Node instances get parent relationship established
     *
     * @return string The concatenated string representation of all mapped items, empty string if no items
     */
    public function __toString(): string
    {
        if ($this->arr === null) {
            return '';
        }

        $shadowNode = new Node;
        $mapFunc = is_callable($this->mapFunction) ? Closure::fromCallable($this->mapFunction) : null;

        if ($mapFunc && $this->parent !== null) {
            $mapFunc = $mapFunc->bindTo($this->parent);
        }

        foreach ($this->arr as $key => $current) {
            $item = $mapFunc ? $mapFunc($current, $key) : $current;
            $shadowNode->appendChild($item);
            if ($item instanceof Node) {
                $item->setParent($this->parent);
            }
        }

        return $shadowNode->__toString();
    }
}
