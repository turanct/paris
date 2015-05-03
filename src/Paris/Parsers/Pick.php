<?php

namespace Paris\Parsers;

use Paris\Parser;
use Paris\Maybe\Nothing;

final class Pick implements Parser
{
    private $left;
    private $right;
    private $side;

    public function __construct(Parser $left, Parser $right, $side)
    {
        $this->left = $left;
        $this->right = $right;
        $this->side = $side;
    }

    public function parse($string)
    {
        $left = $this->left->parse($string);
        if ($left instanceof Nothing) {
            return new Nothing();
        }

        $right = $this->right->parse($left->get()->getRemainingString());
        if ($right instanceof Nothing) {
            return new Nothing();
        }

        return \Paris\parseResult(
            ${$this->side}->get()->getResult(),
            $right->get()->getRemainingString()
        );
    }
}

