<?php

namespace IceTea\IceDOM;

use Closure;
use Stringable;

use function is_callable;

/**
 * ArrayMap is a utility class that maps an iterable array to string representation.
 *
 * This class allows you to transform an array of data into strings by either
 * directly using the array items or applying a mapping function to transform
 * each item before rendering. It implements Stringable for direct string conversion.
 */
class ArrayMap implements Stringable
{
    /**
     * The iterable array to be mapped to string content.
     */
    protected ?iterable $arr;

    /**
     * The mapping function to transform each array item.
     * Can be a callable that accepts ($value, $key) parameters.
     */
    protected $mapFunction;

    /**
     * The parent node context for child node relationships.
     */
    protected ?Node $parent = null;

    /**
     * Create a new ArrayMap instance.
     *
     * @param  iterable|null  $arr  The array or iterable to map to string content
     * @param  callable|null  $mapFunction  Optional function to transform each array item.
     *                                      Function signature: function($value, $key): Node|string
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
     * This allows child nodes generated during mapping to maintain
     * proper parent-child relationships in the DOM tree.
     *
     * @param  Node|null  $parent  The parent node to set
     * @return static Returns $this for method chaining
     */
    public function setParent(?Node $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the parent node context.
     *
     * @return Node|null The parent node, or null if no parent is set
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * Convert the ArrayMap to string representation.
     *
     * This method processes the array and applies the mapping function if provided.
     * Each resulting item is converted to string and concatenated.
     *
     * @return string The concatenated string representation of all mapped items
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
