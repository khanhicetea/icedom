<?php

namespace IceTea\IceDOM;

use Exception;

use function count;

/**
 * IfElseNode - Provides conditional rendering with if-elseif-else logic in DOM trees.
 *
 * Responsibility:
 * This specialized Node implements conditional rendering similar to PHP's if-elseif-else
 * control structures. It maintains multiple condition-children pairs and evaluates them
 * sequentially, rendering only the first matching condition's children or falling back
 * to else children. This enables dynamic, conditional DOM structure building without
 * scattering conditional logic throughout the codebase.
 *
 * How It Works:
 * - Maintains parallel arrays: conditions and children blocks
 * - Each condition has a corresponding children array at the same index
 * - Evaluates conditions in order during __toString()
 * - Renders first truthy condition's children
 * - Falls back to elseChildren if no conditions match
 *
 * Structure:
 * ```
 * conditions[0] => children[0]  // if block
 * conditions[1] => children[1]  // elseif block
 * conditions[2] => children[2]  // elseif block
 * (no condition) => elseChildren // else block
 * ```
 *
 * Use Cases:
 * - Conditional component rendering
 * - Dynamic content based on state
 * - Multi-branch display logic
 * - Feature flags or permission-based rendering
 *
 * Example:
 * ```php
 * $node = new IfElseNode(['Guest content'], [], fn() => $isLoggedIn)
 *     ->elseif(fn() => $isPremium)(['Premium content'])
 *     ->else('Standard content');
 * // Renders based on which condition is true
 * ```
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 * @see Node Base class for children rendering
 * @see SlotNode For simpler dynamic/fallback content
 */
class IfElseNode extends Node
{
    /**
     * Array of conditions to evaluate for each conditional block.
     *
     * Each condition can be:
     * - bool: Direct true/false value
     * - Closure: Lazy-evaluated function returning bool/truthy value
     * - Other: Any truthy/falsy value
     *
     * @var array<bool|Closure|mixed> Parallel to children array by index
     */
    protected $conditions = [];

    /**
     * Children to render when no conditions evaluate to true (else block).
     *
     * @var array<Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null> Fallback content
     */
    protected $elseChildren = [];

    /**
     * Current condition block index being built/modified.
     *
     * Tracks which condition block is active for adding children via __invoke().
     * Increments with each pushCondition() call.
     *
     * @var int Zero-based index, -1 initially (set to 0 after first condition)
     */
    protected $conditionIdx = -1;

    /**
     * Constructs a new IfElseNode with initial condition block and optional else block.
     *
     * Initialization sequence:
     * 1. Bypasses parent Node constructor (no children)
     * 2. Pushes initial condition (creates first condition block)
     * 3. Adds children to the first condition block
     * 4. Sets else children if provided
     *
     * @param array<Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null> $children Initial children for the first (if) condition block.
     *                                                                                             Rendered if first condition is true.
     * @param array<Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null> $elseChildren Children for the else block (no condition).
     *                                                                                               Rendered if all conditions are false.
     * @param bool|Closure|mixed $condition The first condition to evaluate.
     *                                      - bool: Direct true/false value
     *                                      - Closure: Lazy-evaluated function returning truthy/falsy
     *                                      - Other: Any value evaluated for truthiness
     *                                      - null: Always falsy (useful for placeholder logic)
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
     * Adds a new condition and creates a corresponding children block.
     *
     * Internal method called by constructor and elseif(). Increments the condition
     * index to track which condition block is currently active.
     *
     * @param bool|Closure|mixed $condition The condition to add.
     *                                      - Evaluated during __toString() via tryEvalClosure()
     *                                      - Closures evaluated with this node as parameter
     *                                      - All values tested for truthiness
     * @return void
     */
    public function pushCondition($condition)
    {
        $this->conditions[] = $condition;
        $this->conditionIdx++;
    }

    /**
     * Adds children to the else block (fallback when all conditions fail).
     *
     * Else children are rendered only when all conditions evaluate to false.
     * Can be called multiple times to append more else children.
     *
     * @param Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null ...$children Content for else block.
     *                                                                                         Each child type handled as per Node rules.
     *                                                                                         Node children get parent set to this IfElseNode.
     * @return static Returns $this for method chaining
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
     * Adds an elseif conditional block.
     *
     * Creates a new condition that will be evaluated after previous conditions.
     * After calling elseif(), use __invoke() to add children to this condition block.
     *
     * Usage pattern:
     * ```php
     * $node->elseif($condition)(['children', 'for', 'this', 'condition']);
     * ```
     *
     * @param bool|Closure|mixed $condition The condition to evaluate.
     *                                      - bool: Direct boolean value
     *                                      - Closure: Lazy-evaluated, receives this node as parameter
     *                                      - Other: Evaluated for truthiness
     * @return static Returns $this for method chaining (enables ->(...$children) call)
     */
    public function elseif($condition): static
    {
        $this->pushCondition($condition);

        return $this;
    }

    /**
     * Adds children to the current condition block using function-call syntax.
     *
     * Magic method enabling fluent syntax for adding children to the active condition.
     * Throws exception if called after else() to maintain logical order.
     *
     * Example:
     * ```php
     * $node = new IfElseNode([], [], $condition1)
     *     (['Content for condition 1'])
     *     ->elseif($condition2)(['Content for condition 2'])
     *     ->else('Else content');
     * ```
     *
     * @param Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null ...$children Content for current condition block.
     *                                                                                         Added to children array at current conditionIdx.
     *                                                                                         Node children get parent reference set.
     * @return static Returns $this for method chaining
     * @throws Exception If called after else() has been invoked (elseChildren not empty)
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
     * Converts IfElseNode to string by evaluating conditions and rendering matching block.
     *
     * Evaluation process:
     * 1. Iterates through conditions array in order (0 to conditionIdx)
     * 2. Evaluates each condition via tryEvalClosure() (executes Closures)
     * 3. Renders children of first truthy condition using SlotNode
     * 4. If no conditions match, renders elseChildren using SlotNode
     *
     * Condition evaluation:
     * - Closures: Executed with this node as parameter, result tested for truthiness
     * - Other types: Tested directly for truthiness
     * - First truthy condition wins (short-circuit evaluation)
     *
     * @return string The rendered content of first matching condition block or else block
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
