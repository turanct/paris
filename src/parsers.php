<?php

namespace paris;

use InvalidArgumentException;

// satisfy :: Parser String
function satisfy($predicate)
{
    return parser(
        function ($string) use ($predicate) {
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
        }
    );
}

// character :: Parser String
function character($character)
{
    if (empty($character)) {
        throw new InvalidArgumentException('A character should be given');
    }

    if (strlen($character) > 1) {
        throw new InvalidArgumentException('One character should be given');
    }

    return parseOrFail(
        satisfy(
            function ($firstCharacter) use ($character) {
                return $firstCharacter == $character;
            }
        ),
        "Expected character '{$character}'"
    );
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

    return parseOrFail(
        call_user_func_array('paris\\sequence', $characterParsers),
        "Expected string '{$string}'"
    );
}

// many :: Parser String
function many(Parser $parser)
{
    return parser(
        function ($string) use ($parser) {
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
        }
    );
}

// many1 :: Parser String
function many1(Parser $parser)
{
    return parser(
        fmap(
            sequence(
                fmap(
                    $parser,
                    function ($result) {
                        return array($result);
                    }
                ),
                many($parser)
            ),
            function ($arrays) {
                return call_user_func_array('array_merge', $arrays);
            }
        )
    );
}

// sequence :: Parser String
function sequence($parsers)
{
    $parsers = func_get_args();

    return parser(
        function ($string) use ($parsers) {
            foreach ($parsers as $parser) {
                $result = $parser($string);
                if (isFailure($result)) {
                    return $result;
                }

                $resultData = unwrap($result);
                if (!isset($resultSet)) {
                    $resultSet = array($resultData);
                } else {
                    $resultSet[] = $resultData;
                }

                $string = remainingString($result);
            }

            return success(
                $resultSet,
                $string
            );
        }
    );
}

// choice :: Parser String
function choice($parsers)
{
    $parsers = func_get_args();

    return parser(
        function ($string) use ($parsers) {
            foreach ($parsers as $parser) {
                // $result = $parser($string);
                $result = call_user_func($parser, $string);

                if (!isFailure($result)) {
                    return $result;
                }
            }

            return failure('Did not match any of the given choices');
        }
    );
}

// optional :: Parser String
function optional(Parser $parser)
{
    return parser(
        function ($string) use ($parser) {
            $result = $parser($string);

            if (!isFailure($result)) {
                return $result;
            }

            return success('', $string);
        }
    );
}

// left :: Parser String
function left(Parser $left, Parser $right)
{
    return parser(pick($left, $right, @left));
}

// right :: Parser String
function right(Parser $left, Parser $right)
{
    return parser(pick($left, $right, @right));
}

// pick :: Parser String
function pick(Parser $left, Parser $right, $side)
{
    if (!in_array($side, array(@left, @right))) {
        throw new InvalidArgumentException('Side should be left or right');
    }

    return parser(
        function ($string) use ($left, $right, $side) {
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
        }
    );
}

// surroundedBy :: Parser String
function surroundedBy($start, $end)
{
    return parser(
        fmap(
            right(
                string($start),
                left(many1(not(string($end))), string($end))
            ),
            'implode'
        )
    );
}

// oneOf :: Parser String
function oneOf(array $characters)
{
    return parser(
        satisfy(
            function ($firstCharacter) use ($characters) {
                return in_array($firstCharacter, $characters);
            }
        )
    );
}

// noneOf :: Parser String
function noneOf(array $characters)
{
    return parser(not(oneOf($characters)));
}

// not :: Parser String
function not(Parser $parser)
{
    return parser(
        function ($string) use ($parser) {
            $result = parse($parser, $string);

            if (isFailure($result)) {
                return success(
                    substr($string, 0, 1),
                    substr($string, 1)
                );
            }

            return failure('Parser was not supposed to match');
        }
    );
}

// whitespace :: Parser String
function whitespace()
{
    return parser(
        choice(
            character(' '),
            character("\t")
        )
    );
}

// eol :: Parser String
function eol()
{
    return parser(
        choice(
            character("\n"),
            string("\r\n")
        )
    );
}

// line :: Parser String
function line()
{
    return parser(
        fmap(
            left(
                many1(not(eol())),
                optional(eol())
            ),
            'implode'
        )
    );
}

// fmap :: Parser String
function fmap(Parser $parser, $f)
{
    return parser(
        function ($string) use ($parser, $f) {
            $result = $parser($string);

            if (!isFailure($result)) {
                return success(
                    $f(unwrap($result)),
                    remainingString($result)
                );
            }

            return $result;
        }
    );
}

// parseOrFail :: Parser String
function parseOrFail(Parser $parser, $message = '')
{
    return parser(
        function ($string) use ($parser, $message) {
            $result = parse($parser, $string);

            if (isFailure($result)) {
                return failure($message);
            }

            return $result;
        }
    );
}
