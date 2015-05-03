<?php

namespace Paris\Parsers;

use Paris\Parser;

final class Right implements Parser
{
    private $parser;

    public function __construct(Parser $left, Parser $right)
    {
        $this->parser = new Pick($left, $right, @right);
    }

    public function parse($string)
    {
        return $this->parser->parse($string);
    }
}
