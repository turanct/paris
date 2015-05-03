<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Just;
use Paris\Maybe\Nothing;

final class One implements Parser
{
    private $parsers;

    public function __construct($parsers)
    {
        $this->parsers = func_get_args();
    }

    public function parse($string)
    {
        foreach ($this->parsers as $parser) {
            $result = $parser->parse($string);
            if ($result instanceof Just) {
                return $result;
            }
        }

        return new Nothing();
    }
}
