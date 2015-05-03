<?php

namespace Paris;

interface Parser
{
    /**
     * Parse a string
     *
     * @param string $string The string that we want to parse
     *
     * @return Maybe{ParseResult}
     */
    public function parse($string);
}

