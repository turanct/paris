<?php

namespace paris;

// data ParseResult a = Success a | Failure
interface ParseResult
{
    // unwrap :: ParseResult a -> a
    public function unwrap();

    // fmap :: ParseResult a -> (a -> b) -> ParseResult b
    public function fmap($f);

    // bind :: ParseResult a -> (a -> ParseResult b) -> ParseResult b
    public function bind($f);
}

final class Success implements ParseResult
{
    private $result;
    private $remainingString;

    public function __construct($result, $remainingString)
    {
        $this->result = $result;
        $this->remainingString = $remainingString;
    }

    // unwrap :: ParseResult a -> a
    public function unwrap()
    {
        return $this->result;
    }

    // fmap :: ParseResult a -> (a -> b) -> ParseResult b
    public function fmap($f)
    {
        return new static($f($this->result), $this->remainingString);
    }

    // bind :: ParseResult a -> (a -> ParseResult b) -> ParseResult b
    public function bind($f)
    {
        $newResult = $f($this->remainingString);

        if ($newResult instanceof Failure) {
            return $newResult;
        }

        return new static(
            array($this->result, $newResult->unwrap()),
            $newResult->getRemainingString()
        );
    }

    public function getRemainingString()
    {
        return $this->remainingString;
    }
}

final class Failure implements ParseResult
{
    private $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    // unwrap :: ParseResult a -> a
    public function unwrap()
    {
        return $this->reason;
    }

    // fmap :: ParseResult a -> (a -> b) -> ParseResult b
    public function fmap($f)
    {
        return clone $this;
    }

    // bind :: ParseResult a -> (a -> ParseResult b) -> ParseResult b
    public function bind($f)
    {
        return clone $this;
    }
}

function success($result, $remainingString)
{
    return new Success($result, $remainingString);
}

function unwrap(ParseResult $result)
{
    return $result->unwrap();
}

function remainingString(Success $result)
{
    return $result->getRemainingString();
}

function failure($reason)
{
    return new Failure($reason);
}

function isFailure($result)
{
    return $result instanceof Failure;
}
