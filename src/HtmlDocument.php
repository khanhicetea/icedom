<?php

namespace IceTea\IceDOM;

use function implode;

class HtmlDocument extends HtmlNode
{
    public function __toString()
    {
        return implode("\n", [
            '<!DOCTYPE html>',
            parent::__toString(),
        ]);
    }
}
