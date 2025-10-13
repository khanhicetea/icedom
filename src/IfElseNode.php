<?php

namespace IceTea\IceDOM;

use Exception;

use function count;

/**
 * Represents a conditional node that supports if-elseif-else logic in the DOM tree.
 *
 * This node allows for conditional rendering of content based on multiple conditions.
 * It behaves similarly to PHP's if-elseif-else statements but in a DOM context.
 */
class IfElseNode extends Node
{
    /**
     * Array of conditions to evaluate for each if/elseif block.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Array of child nodes to render when no conditions are met (else block).
     *
     * @var array
     */
    protected $elseChildren = [];

    /**
     * Current condition index, tracks which condition block is being built.
     *
     * @var int
     */
    protected $conditionIdx = -1;

    /**
     * Constructs a new IfElseNode.
     *
     * @param  array  $children  Initial children for the first condition block
     * @param  array  $elseChildren  Children for the else block
     * @param  mixed  $condition  The initial condition to evaluate
     */
    public function __construct(
        array $children = [],
        array $elseChildren = [],
        $condition = null,
    ) {
        parent::__construct([]);
        $this->pushCondition($condition);
        
        // Add initial children to the first condition block
        foreach ($children as $child) {
            if (! isset($this->children[$this->conditionIdx])) {
                $this->children[$this->conditionIdx] = [];
            }
            $this->children[$this->conditionIdx][] = $child;
            
            if ($child instanceof Node) {
                $child->setParent($this);
            }
        }
        
        $this->else(...$elseChildren);
    }

    /**
     * Adds a new condition to the conditions array and increments the condition index.
     *
     * @param  mixed  $condition  The condition to add (can be a boolean, callable, or any evaluable value)
     * @return void
     */
    public function pushCondition($condition)
    {
        $this->conditions[] = $condition;
        $this->conditionIdx++;
    }

    /**
     * Adds children to the else block.
     *
     * These children will be rendered when no conditions evaluate to true.
     *
     * @param  mixed  ...$children  The child nodes to add to the else block
     * @return static Returns the current instance for method chaining
     */
    public function else(...$children): static
    {
        foreach ($children as $child) {
            $this->elseChildren[] = $child;

            if ($child instanceof Node) {
                $child->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Adds an elseif condition to the node.
     *
     * This creates a new condition block that will be evaluated if previous conditions fail.
     *
     * @param  mixed  $condition  The condition to evaluate for this elseif block
     * @return static Returns the current instance for method chaining
     */
    public function elseif($condition): static
    {
        $this->pushCondition($condition);

        return $this;
    }

    /**
     * Adds children to the current condition block.
     *
     * This magic method allows adding children using function call syntax.
     * Children are added to the current condition block based on the condition index.
     *
     * @param  mixed  ...$children  The child nodes to add to the current condition block
     * @return static Returns the current instance for method chaining
     *
     * @throws Exception If children are added after else children have been set
     */
    public function __invoke(...$children): static
    {
        if (count($this->elseChildren) > 0) {
            throw new Exception("Please don't add children after else children!");
        }

        $idx = $this->conditionIdx;
        if (! isset($this->children[$idx])) {
            $this->children[$idx] = [];
        }

        foreach ($children as $child) {
            $this->children[$idx][] = $child;

            if ($child instanceof Node) {
                $child->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Converts the node to its string representation.
     *
     * Evaluates all conditions in order and returns the string representation
     * of the first condition block that evaluates to true. If no conditions
     * evaluate to true, returns the else block content.
     *
     * @return string The rendered content of the matching condition block or else block
     */
    public function __toString()
    {
        for ($i = 0; $i <= $this->conditionIdx; $i++) {
            if ($this->tryEvalClosure($this->conditions[$i])) {
                return (string) (new SlotNode($this->children[$i] ?? []));
            }
        }

        return (string) (new SlotNode($this->elseChildren));
    }
}
