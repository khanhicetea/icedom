<?php

namespace IceTea\IceDOM;

use Closure;
use Exception;

use function is_callable;

class ArrayMapNode extends Node
{
    protected ?iterable $arr;

    protected $mapFunction;

    public function __construct(
        array $children = [],
        ?iterable $arr = null,
        $mapFunction = null,
    ) {
        parent::__construct($children);
        $this->arr = $arr;
        $this->mapFunction = $mapFunction;
    }

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

    public function appendChild($child): static
    {
        throw new Exception('Can not append child directly into ArrayMapNode !');
    }
}
