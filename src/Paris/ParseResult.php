<?php

namespace Paris;

final class ParseResult
{
    private $result;
    private $remainingString;

    public function __construct($result, $remainingString)
    {
        $this->result = $result;
        $this->remainingString = (string) $remainingString;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getRemainingString()
    {
        return $this->remainingString;
    }
}

