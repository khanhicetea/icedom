<?php

namespace IceTea\IceDOM;

class SafeString
{
    public function __construct(
        protected string $value = ''
    ) {}

    public function __toString()
    {
        return $this->value;
    }
}
