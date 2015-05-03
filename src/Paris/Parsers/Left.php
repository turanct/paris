<?php

namespace Paris\Parsers;

use Paris\Parser;

final class Left implements Parser
{
    private $parser;

    public function __construct(Parser $left, Parser $right)
    {
        $this->parser = new Pick($left, $right, @left);
    }

    public function parse($string)
    {
        return $this->parser->parse($string);
    }
}
