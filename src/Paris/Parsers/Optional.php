<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Just;

final class Optional implements Parser
{
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse($string)
    {
        $result = $this->parser->parse($string);

        if ($result instanceof Just) {
            return $result;
        }

        return \Paris\parseResult('', $string);
    }
}
