<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Nothing;

final class NoneOf implements Parser
{
    private $characters;

    public function __construct(array $characters)
    {
        $this->characters = $characters;
    }

    public function parse($string)
    {
        if (
            strlen($string) === 0
            || in_array(substr($string, 0, 1), $this->characters)
        ) {
            return new Nothing();
        }

        return \Paris\parseResult(
            substr($string, 0, 1),
            substr($string, 1)
        );
    }
}
