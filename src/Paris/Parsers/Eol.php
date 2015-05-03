<?php

namespace Paris\Parsers;

use Paris\Parser;

final class Eol implements Parser
{
    public function parse($string)
    {
        $parser = new One(
            new Character("\n"),
            new String("\r\n")
        );

        return $parser->parse($string);
    }
}
