<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Nothing;

final class Many implements Parser
{
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse($string)
    {
        $result = array();
        $lastResult = true;

        while (strlen($string) > 0) {
            $lastResult = $this->parser->parse($string);

            if ($lastResult == new Nothing()) {
                break;
            }

            $string = $lastResult->get()->getRemainingString();
            $result[] = $lastResult->get()->getResult();
        }

        if (empty($result)) {
            return new Nothing();
        }

        return \Paris\parseResult($result, $string);
    }
}
