<?php

namespace paris;

// satisfy :: Parser String
function satisfy($predicate)
{
    return parser(function($string) use ($predicate) {
        if (strlen($string) == 0) {
            return failure('Unexpected end of input');
        }

        $firstCharacter = substr($string, 0, 1);

        if ($predicate($firstCharacter) !== true) {
            return failure('Character could not be matched');
        }

        return success(
            $firstCharacter,
            substr($string, 1)
        );
    });
}

// character :: Parser String
function character($character)
{
    if (empty($character)) {
        throw new InvalidArgumentException('A character should be given');
    }

    return satisfy(function($firstCharacter) use ($character) {
        return $firstCharacter == $character;
    });
}

// string :: Parser String
function string($string)
{
    $characterParsers = array_map(
        function ($character) {
            return character($character);
        },
        preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY)
    );

    return call_user_func_array('paris\\sequence', $characterParsers);
}

// many :: Parser String
function many(Parser $parser)
{
    return parser(function($string) use ($parser) {
        $result = array();
        $lastResult = true;

        while (strlen($string) > 0) {
            $lastResult = $parser($string);

            if (isFailure($lastResult)) {
                break;
            }

            $string = remainingString($lastResult);
            $result[] = unwrap($lastResult);
        }

        return success($result, $string);
    });
}

// many1 :: Parser String
function many1(Parser $parser)
{
    return sequence(
        fmap($parser, function($result) { return array($result); }),
        many($parser)
    );
}

// sequence :: Parser String
function sequence($parsers)
{
    $parsers = func_get_args();

    return parser(function($string) use ($parsers) {
        foreach ($parsers as $parser) {
            $result = $parser($string);
            if (isFailure($result)) {
                return $result;
            }

            $resultData = unwrap($result);
            if (!isset($resultSet)) {
                $resultSet = $resultData;
            } elseif (is_array($resultSet) && is_array($resultData)) {
                $resultSet = array_merge($resultSet, $resultData);
            } elseif (is_array($resultSet)) {
                $resultSet[] = $resultData;
            } else {
                $resultSet .= $resultData;
            }

            $string = remainingString($result);
        }

        return success(
            $resultSet,
            $string
        );
    });
}

// choice :: Parser String
function choice($parsers)
{
    $parsers = func_get_args();

    return parser(function($string) use ($parsers) {
        foreach ($parsers as $parser) {
            $result = $parser($string);

            if (!isFailure($result)) {
                return $result;
            }
        }

        return failure('Did not match any of the given parsers');
    });
}

// optional :: Parser String
function optional(Parser $parser)
{
    return parser(function ($string) use ($parser) {
        $result = $parser($string);

        if (!isFailure($result)) {
            return $result;
        }

        return success('', $string);
    });
}

// left :: Parser String
function left(Parser $left, Parser $right)
{
    return pick($left, $right, @left);
}

// right :: Parser String
function right(Parser $left, Parser $right)
{
    return pick($left, $right, @right);
}

// pick :: Parser String
function pick(Parser $left, Parser $right, $side)
{
    if (!in_array($side, array(@left, @right))) {
        throw new InvalidArgumentException('Side should be left or right');
    }

    return parser(function ($string) use ($left, $right, $side) {
        $leftResult = $left($string);
        if (isFailure($leftResult)) {
            return $leftResult;
        }

        $rightResult = $right(remainingString($leftResult));
        if (isFailure($rightResult)) {
            return $rightResult;
        }

        return success(
            unwrap(${$side . 'Result'}),
            remainingString($rightResult)
        );
    });
}

// oneOf :: Parser String
function oneOf(array $characters)
{
    return satisfy(function($firstCharacter) use ($characters) {
        return in_array($firstCharacter, $characters);
    });
}

// noneOf :: Parser String
function noneOf(array $characters)
{
    return satisfy(function($firstCharacter) use ($characters) {
        return !in_array($firstCharacter, $characters);
    });
}

// not :: Parser String
function not(Parser $parser)
{
    return satisfy(function($firstCharacter) use ($parser) {
        $result = parse($parser, $firstCharacter);

        if (isFailure($result)) {
            return true;
        }

        return false;
    });
}

// eol :: Parser String
function eol()
{
    return choice(
        character("\n"),
        string("\r\n")
    );
}

// line :: Parser String
function line()
{
    return fmap(
        left(
            many(not(eol())),
            optional(eol())
        ),
        'implode'
    );
}

// fmap :: Parser String
function fmap(Parser $parser, $f)
{
    return parser(function ($string) use ($parser, $f) {
        $result = $parser($string);

        if (!isFailure($result)) {
            return success(
                $f(unwrap($result)),
                remainingString($result)
            );
        }

        return $result;
    });
}
