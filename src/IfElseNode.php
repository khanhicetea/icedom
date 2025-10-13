<?php

namespace IceTea\IceDOM;

use Exception;

use function count;

class IfElseNode extends Node
{
    protected $conditions = [];

    protected $elseChildren = [];

    protected $conditionIdx = -1;

    public function __construct(
        array $children = [],
        array $elseChildren = [],
        $condition = null,
    ) {
        parent::__construct($children);
        $this->else(...$elseChildren);
        $this->pushCondition($condition);
    }

    public function pushCondition($condition)
    {
        $this->conditions[] = $condition;
        $this->conditionIdx++;
    }

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

    public function elseif($condition): static
    {
        $this->pushCondition($condition);

        return $this;
    }

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

    public function __toString()
    {
        for ($i = 0; $i <= $this->conditionIdx; $i++) {
            if ($this->tryEvalClosure($this->conditions[$i])) {
                return (string) (new SlotNode($this->children[$i]));
            }
        }

        return (string) (new SlotNode($this->elseChildren));
    }

    // TODO : String buffering
    // public function echo()
    // {
    //     for ($i = 0; $i <= $this->conditionIdx; $i++) {
    //         if ($this->tryEvalClosure($this->conditions[$i])) {
    //             (new SlotNode($this->children[$i]))->echo();
    //             return;
    //         }
    //     }
    //
    //     (new SlotNode($this->elseChildren))->echo();
    // }
}
