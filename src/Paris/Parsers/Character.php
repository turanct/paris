<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Nothing;

final class Character implements Parser
{
    private $character;

    public function __construct($character = null)
    {
        $this->character = $character;
    }

    public function parse($string)
    {
        if (strlen($string) == 0) {
            return new Nothing();
        }

        if (
            $this->character !== null
            && substr($string, 0, 1) != $this->character
        ) {
            return new Nothing();
        }

        return \Paris\parseResult(
            substr($string, 0, 1),
            substr($string, 1)
        );
    }
}
