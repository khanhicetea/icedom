<?php

namespace IceTea\IceDOM;

class RawNode extends Node
{
    public function __toString()
    {
        if (count($this->children) == 0) {
            return '';
        }

        return implode(' ', $this->children);
    }

    // TODO : String buffering
    // public function echo()
    // {
    //     foreach ($this->children as $child) {
    //         echo $child;
    //     }
    // }
}
