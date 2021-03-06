<?php

namespace XmlParser;

use function paris\parse;
use function paris\parser;
use function paris\character;
use function paris\string;
use function paris\whitespace;
use function paris\eol;
use function paris\sequence;
use function paris\right;
use function paris\left;
use function paris\surroundedBy;
use function paris\not;
use function paris\many;
use function paris\many1;
use function paris\noneOf;
use function paris\choice;
use function paris\recur;
use function paris\fmap;
use function paris\parseOrFail;
use function paris\isFailure;

require_once __DIR__ . '/../../vendor/autoload.php';

// First, we prepare the datatypes that we'll use to represent an XML structure in memory after it's parsed:
// We define an `Attribute`, `OpeningTag`, `TagNode` and `TextNode`. The `OpeningTag` is a temporary structure
// that's only used during parsing.

final class Attribute
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = (string) $key;
        $this->value = (string) $value;
    }
}

final class OpeningTag
{
    private $name;
    private $attributes;

    public function __construct($name, array $attributes)
    {
        $this->name = (string) $name;
        $this->attributes = $attributes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}

final class TagNode
{
    private $name;
    private $attributes;
    private $children;

    public function __construct($name, array $attributes, array $children)
    {
        $this->name = (string) $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }
}

final class TextNode
{
    private $text;

    public function __construct($text)
    {
        $this->text = (string) $text;
    }
}

// Next up, we define the different parsers that we'll combine to form an XML parser:
// `attribute()`, `openingTag()`, `closingTag()`, and `text()`. These parsers can be
// used either by themselves to parse what they represent, or in a bigger combination.
// They already return the correct datatypes, e.g. `attribute()` will return
// `Attribute` instances.

function attribute()
{
    return parseOrFail(
        fmap(
            sequence(
                many1(choice(whitespace(), eol())),
                many1(not(character('='))),
                character('='),
                surroundedBy('"', '"')
            ),
            function ($result) {
                return new Attribute(
                    implode($result[1]),
                    $result[3]
                );
            }
        ),
        'Expected attribute'
    );
}

function openingTag()
{
    return parseOrFail(
        fmap(
            right(
                many(choice(whitespace(), eol())),
                left(
                    sequence(
                        character('<'),
                        many1(noneOf(array('>', ' ', '/', '<', "\n"))),
                        many(attribute()),
                        character('>')
                    ),
                    many(choice(whitespace(), eol()))
                )
            ),
            function ($result) {
                return new OpeningTag(
                    implode($result[1]),
                    $result[2]
                );
            }
        ),
        'Expected opening tag'
    );
}

function closingTag($name)
{
    return parseOrFail(
        right(
            many(choice(whitespace(), eol())),
            left(
                string("</{$name}>"),
                many(choice(whitespace(), eol()))
            )
        ),
        'Expected closing tag'
    );
}

function text()
{
    return parseOrFail(
        fmap(
            fmap(
                many1(
                    not(
                        sequence(
                            many(choice(whitespace(), eol())),
                            character('<')
                        )
                    )
                ),
                'implode'
            ),
            function ($result) {
                return new TextNode($result);
            }
        ),
        'Expected text node'
    );
}

// Here we combine the different parsers that we created before into a recursive
// parser that can parse nested XML tags. It just defines the parser, it doesn't
// do anything yet.

function tag()
{
    return parser(
        function ($string) {
            $openingTag = parse(openingTag(), $string);

            $children = $openingTag->bind(
                many(
                    choice(
                        recur('XmlParser\\tag'),
                        recur('XmlParser\\text')
                    )
                )
            );

            $tagName = !isFailure($openingTag) ? $openingTag->unWrap()->getName() : '';

            $closingTag = $children->bind(closingTag($tagName));

            return $closingTag->fmap(
                function ($tag) {
                    return new TagNode(
                        $tag[0][0]->getName(),
                        $tag[0][0]->getAttributes(),
                        $tag[0][1]
                    );
                }
            );
        }
    );
}

// Finally, we can run the parser with some XML:

$test = '
    <test foo="bar">
        <baz>
            ramsam
        </baz>
    </test>
';

var_dump(parse(tag(), $test));
