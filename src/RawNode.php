<?php

namespace IceTea\IceDOM;

class RawNode extends Node
{
    public function __toString()
    {
        return implode(' ', $this->children);
    }
}
