<?php

namespace IceTea\IceDOM;

use Closure;

use function call_user_func;

/**
 * SlotNode represents a dynamic node that can render content either through a callable function
 * or its child elements.
 *
 * This class extends Node and provides flexibility in rendering content by allowing either:
 * - A closure function that generates the content dynamically
 * - Static child elements that are rendered as-is
 */
class SlotNode extends Node
{
    /**
     * The closure function that will be called to generate the node's content.
     * If null, the node will render its children instead.
     *
     * @var Closure|null
     */
    protected $slotFunction;

    /**
     * Create a new SlotNode instance.
     *
     * @param  array  $children  Array of child nodes to be rendered when no slot function is provided
     * @param  Closure|null  $slotFunction  Optional closure that generates the node's content when called
     */
    public function __construct(
        array $children = [],
        ?Closure $slotFunction = null,
    ) {
        parent::__construct($children);
        $this->slotFunction = $slotFunction;
    }

    /**
     * Convert the SlotNode to its string representation.
     *
     * If a slot function is provided, it will be called and its return value will be cast to string.
     * Otherwise, the node's children will be rendered as a string.
     *
     * @return string The rendered content of the node
     */
    public function __toString()
    {
        if ($this->slotFunction instanceof Closure) {
            return (string) call_user_func($this->slotFunction);
        }

        return $this->childrenToString();
    }
}
