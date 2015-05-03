<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Nothing;

final class String implements Parser
{
    private $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function parse($string)
    {
        if (
            substr($string, 0, strlen($this->string)) == $this->string
        ) {
            return \Paris\parseResult(
                $this->string,
                substr($string, strlen($this->string))
            );
        }

        return new Nothing();
    }
}
