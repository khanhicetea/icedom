<?php

namespace IceTea\IceDOM;

use Closure;
use Stringable;

use function count;
use function htmlspecialchars;
use function implode;

abstract class Node
{
    public const ENT_FLAGS = ENT_QUOTES | ENT_HTML5;

    protected ?Node $parent = null;

    protected array $children = [];

    public function __construct(
        array $children = [],
    ) {
        $this->appendChildren($children);
    }

    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function use(Closure|callable|null $hook): static
    {
        $hook = is_callable($hook) ? Closure::fromCallable($hook) : $hook;

        if ($hook instanceof Closure) {
            $hook->call($this, $this);
        }

        return $this;
    }

    public function childrenUse(Closure|callable|null $hook): static
    {
        $hook = is_callable($hook) ? Closure::fromCallable($hook) : $hook;

        if ($hook instanceof Closure) {
            foreach ($this->children as $child) {
                $hook->call($this, $child);
            }
        }

        return $this;
    }

    public function clearChildren(): static
    {
        $this->children = [];

        return $this;
    }

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

    public function appendChildren(array $children): static
    {
        foreach ($children as $child) {
            $this->appendChild($child);
        }

        return $this;
    }

    public function map(iterable $arr, $mapFunction = null): static
    {
        return $this->appendChild(new ArrayMapNode([], $arr, $mapFunction));
    }

    public function __invoke(...$children): static
    {
        return $this->appendChildren($children);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

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

            if ($child instanceof Node) {
                $child->setParent($this);
                $strArr[] = $child->__toString();
            } elseif (is_string($child)) {
                $strArr[] = htmlspecialchars($child, static::ENT_FLAGS);
            } elseif (is_int($child) || is_float($child) || ($child instanceof SafeString)) {
                $strArr[] = (string) $child;
            } elseif ($child instanceof Stringable) {
                $strArr[] = htmlspecialchars($child, static::ENT_FLAGS);
            }
        }

        return implode(' ', $strArr);
    }

    protected function tryEvalClosure($value)
    {
        if ($value instanceof Closure) {
            return $value($this);
        }

        return $value;
    }

    abstract public function __toString();

    // TODO : String buffering
    // abstract public function echo();

    // TODO : String buffering
    // protected function echoChildren()
    // {
    //     foreach ($this->children as $child) {
    //         if ($child instanceof Closure) {
    //             $child = $child($this);
    //         }
    //
    //         if ($child instanceof Node) {
    //             $child->setParent($this);
    //             $child->echo();
    //             continue;
    //         }
    //
    //         echo \htmlspecialchars($child, static::ENT_FLAGS);
    //     }
    // }
}
