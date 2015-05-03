<?php

namespace Paris\Maybe;

final class Just implements Maybe
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function bind($function)
    {
        return new Just($function($this->value));
    }
}
