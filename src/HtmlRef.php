<?php

namespace IceTea\IceDOM;

use function base_convert;
use function intval;

class HtmlRef
{
    public static int $base = 0;

    public function __construct(private string $ref = '')
    {
        HtmlRef::$base = (HtmlRef::$base ?: intval(microtime(true) * 1000)) + 1;
        $this->ref = $ref ?: '_'.base_convert(HtmlRef::$base, 10, 36);
    }

    public function __toString()
    {
        return $this->ref;
    }
}
