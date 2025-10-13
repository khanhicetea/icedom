<?php

namespace IceTea\IceDOM;

use Closure;
use Exception;

use function is_callable;

/**
 * ArrayMapNode is a specialized Node that maps an iterable array to child nodes.
 *
 * This class allows you to transform an array of data into DOM nodes by either
 * directly using the array items as children or applying a mapping function
 * to transform each item before rendering.
 */
class ArrayMapNode extends Node
{
    /**
     * The iterable array to be mapped to child nodes.
     */
    protected ?iterable $arr;

    /**
     * The mapping function to transform each array item.
     * Can be a callable that accepts ($value, $key) parameters.
     */
    protected $mapFunction;

    /**
     * Create a new ArrayMapNode instance.
     *
     * @param  array  $children  Initial child nodes (empty by default)
     * @param  iterable|null  $arr  The array or iterable to map to child nodes
     * @param  callable|null  $mapFunction  Optional function to transform each array item.
     *                                      Function signature: function($value, $key): Node|string
     */
    public function __construct(
        array $children = [],
        ?iterable $arr = null,
        $mapFunction = null,
    ) {
        if (count($children) > 0) {
            throw new Exception('Can not init non-empty children directly into ArrayMapNode !');
        }

        parent::__construct([]); // Children always empty
        $this->arr = $arr;
        $this->mapFunction = $mapFunction;
    }

    /**
     * Convert the ArrayMapNode to string representation.
     *
     * This method processes the array and applies the mapping function if provided.
     * Each resulting item is added as a child node and then rendered.
     * The children array is reset after each render to prevent memory leaks.
     *
     * @return string The rendered string representation of all mapped children
     */
    public function __toString()
    {
        if ($this->arr === null) {
            return '';
        }

        $parent = $this->getParent();
        $mapFunc = $this->mapFunction;

        if ($this->mapFunction === null) {
            foreach ($this->arr as $child) {
                if ($child instanceof Node) {
                    $child->setParent($parent);
                }
                $this->children[] = $child;
            }
        } elseif (is_callable($this->mapFunction)) {
            $mapFunc = Closure::fromCallable($this->mapFunction)->bindTo($parent);
            foreach ($this->arr as $key => $current) {
                $child = $mapFunc($current, $key);
                if ($child instanceof Node) {
                    $child->setParent($parent);
                }
                $this->children[] = $child;
            }
        }

        $result = $this->childrenToString();
        unset($this->children);
        $this->children = [];

        return $result;
    }

    /**
     * Append a child node to this ArrayMapNode.
     *
     * This method is disabled for ArrayMapNode as children are generated
     * dynamically from the array and mapping function during rendering.
     *
     * @param  mixed  $child  The child node to append
     *
     * @throws Exception Always throws as direct appending is not supported
     */
    public function appendChild($child): static
    {
        throw new Exception('Can not append child directly into ArrayMapNode !');
    }
}
