<?php

namespace Paris;

use Paris\Parser;
use Paris\ParseResult;
use Paris\Maybe\Just;

function parseResult($result, $remainingString)
{
    return new Just(new ParseResult($result, $remainingString));
}

function parse(Parser $parser, $string)
{
    return $parser->parse($string)->bind(
        function(ParseResult $result) {
            return $result->getResult();
        }
    );
}
