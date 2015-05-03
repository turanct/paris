<?php

namespace Paris\Maybe;

final class Nothing implements Maybe
{
    public function bind($function)
    {
        return new Nothing();
    }
}
