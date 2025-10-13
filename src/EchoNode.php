<?php

namespace IceTea\IceDOM;

use function ob_get_clean;
use function ob_start;

class EchoNode extends Node
{
    public function __toString()
    {
        ob_start();
        foreach ($this->children as $child) {
            $this->tryEvalClosure($child);
        }
        $raw = ob_get_clean() ?: '';

        // dump(
        //     $raw,
        //     $this->children
        // );
        return $raw;
    }

    // TODO : String buffering
    // public function echo()
    // {
    //     foreach ($this->children as $child) {
    //         $this->tryEvalClosure($child);
    //     }
    // }
}
