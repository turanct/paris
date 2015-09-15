<?php

namespace paris;

final class Parser
{
    private $parser;

    public function __construct($parser)
    {
        $this->parser = $parser;
    }

    public function __invoke($string)
    {
        $f = $this->parser;

        return $f($string);
    }
}

function parser($f)
{
    return new Parser($f);
}

// parse :: Parser a -> String -> Result a
function parse(Parser $parser, $string)
{
    return $parser($string);
}
