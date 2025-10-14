<?php

namespace IceTea\IceDOM;

use Closure;

use function call_user_func;

/**
 * SlotNode - Provides dynamic content slots with fallback to static children.
 *
 * Responsibility:
 * This specialized Node offers a dual-mode rendering system: either execute a
 * dynamic slot function to generate content, or fall back to rendering static
 * children. It's useful for component systems, template slots, conditional content,
 * and default value patterns where you want to provide a dynamic override capability
 * with a static fallback.
 *
 * Rendering Modes:
 * 1. Slot Function Mode: If slotFunction is set, call it and render the result
 * 2. Children Mode: If no slotFunction, render children normally via Node
 *
 * Use Cases:
 * - Component slot systems (like Vue/React slots)
 * - Conditional rendering with defaults
 * - Lazy-loaded content with fallback
 * - Template placeholders that can be overridden
 * - Default content patterns
 *
 * Difference from Closure children:
 * - Closure as child: Evaluated within children array, mixed with other children
 * - SlotNode with slotFunction: Replaces ALL children rendering
 *
 * Example:
 * ```php
 * // Slot function takes precedence
 * $slot = new SlotNode(
 *     ['Default content'],
 *     fn() => 'Dynamic content'
 * );
 * echo $slot; // Outputs: Dynamic content
 *
 * // No slot function, renders children
 * $slot2 = new SlotNode(['Fallback content']);
 * echo $slot2; // Outputs: Fallback content
 * ```
 *
 * @package IceTea\IceDOM
 * @author IceTea Team
 * @see Node Base class for children rendering
 */
class SlotNode extends Node
{
    /**
     * The optional dynamic slot function that overrides children rendering.
     *
     * @var Closure|null Closure to generate content dynamically; null means use children
     */
    protected $slotFunction;

    /**
     * Create a new SlotNode with optional slot function and fallback children.
     *
     * @param array<Node|Closure|string|int|float|SafeString|Stringable|ArrayMap|null> $children Fallback content when slot function is null.
     *                                                                                             - Used only if slotFunction is not provided
     *                                                                                             - Rendered via parent Node::childrenToString()
     *                                                                                             - All Node child types supported
     * @param Closure|null $slotFunction Optional dynamic content generator.
     *                                    - Closure: Called during __toString(), return value rendered
     *                                    - null: Falls back to rendering children
     *                                    - Signature: function(): Node|string|Stringable|int|float|null
     *                                    - No parameters passed to the closure
     *                                    - Return value cast to string
     */
    public function __construct(
        array $children = [],
        ?Closure $slotFunction = null,
    ) {
        parent::__construct($children);
        $this->slotFunction = $slotFunction;
    }

    /**
     * Convert SlotNode to string using slot function or fallback to children.
     *
     * Rendering priority:
     * 1. If slotFunction exists: Call it, cast result to string, ignore children
     * 2. If slotFunction is null: Render children via parent childrenToString()
     *
     * Slot function behavior:
     * - Called with no parameters
     * - Return value cast to string (can be any type)
     * - Replaces children completely (children not rendered at all)
     * - Any exceptions from closure will propagate
     *
     * Children fallback behavior:
     * - Uses Node::childrenToString() with all escaping/evaluation rules
     * - Only used when slotFunction is null
     *
     * @return string The rendered slot content or children content
     */
    public function __toString()
    {
        if ($this->slotFunction instanceof Closure) {
            return (string) call_user_func($this->slotFunction);
        }

        return $this->childrenToString();
    }
}
