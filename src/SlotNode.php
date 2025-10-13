<?php

namespace IceTea\IceDOM;

use Closure;

use function call_user_func;

class SlotNode extends Node
{
    protected $slotFunction;

    public function __construct(
        array $children = [],
        ?Closure $slotFunction = null,
    ) {
        parent::__construct($children);
        $this->slotFunction = $slotFunction;
    }

    public function __toString()
    {
        if ($this->slotFunction instanceof Closure) {
            return (string) call_user_func($this->slotFunction);
        }

        return $this->childrenToString();
    }

    // TODO : String buffering
    // public function echo()
    // {
    //     if ($this->slotFunction instanceof Closure) {
    //         $resolved = $this->tryEvalClosure($this->slotFunction);
    //
    //         if ($resolved instanceof Node) {
    //             $resolved->echo();
    //             return;
    //         }
    //
    //         echo $resolved;
    //     }
    //
    //     $this->echoChildren();
    // }
}
