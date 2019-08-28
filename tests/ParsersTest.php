<?php

namespace paris;

use PHPUnit\Framework\TestCase;

class ParsersTest extends TestCase
{
    /**
     * @test
     */
    public function satisfy_succeeds_when_string_satisfies_predicate()
    {
        $predicate = function ($string) {
            return $string === 'a';
        };
        $parser = satisfy($predicate);

        $expected = success('a', 'ap');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap')
        );
    }

    /**
     * @test
     */
    public function satisfy_fails_when_string_doesnt_satisfy_predicate()
    {
        $predicate = function ($string) {
            return $string === 'a';
        };
        $parser = satisfy($predicate);

        $expected = failure('Character could not be matched');

        $this->assertEquals(
            $expected,
            parse($parser, 'noot')
        );
    }

    /**
     * @test
     */
    public function character_succeeds_when_strings_first_char_is_its_char()
    {
        $parser = character('a');
        $expected = success('a', 'ap');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap')
        );
    }

    /**
     * @test
     */
    public function character_fails_when_strings_first_char_is_not_its_char()
    {
        $parser = character('a');
        $expected = failure('Expected character \'a\'');

        $this->assertEquals(
            $expected,
            parse($parser, 'noot')
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function character_throws_when_nothing_given()
    {
        $parser = character('');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function character_throws_when_string_given()
    {
        $parser = character('aap');
    }

    /**
     * @test
     */
    public function string_succeeds_when_its_the_first_substring()
    {
        $parser = string('aap');
        $expected = success('aap', ' noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function string_fails_when_its_not_the_first_substring()
    {
        $parser = string('noot');
        $expected = failure('Expected string \'noot\'');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function many_parses_a_given_parser_multiple_times_until_it_fails()
    {
        $parser = many(character('a'));
        $expected = success(array('a', 'a'), 'p noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function many_parses_a_given_parser_zero_times_but_doesnt_fail()
    {
        $parser = many(character('p'));
        $expected = success(array(), 'aap noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function many1_parses_a_given_parser_multiple_times_until_it_fails()
    {
        $parser = many1(character('a'));
        $expected = success(array('a', 'a'), 'p noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function many1_parses_a_given_parser_at_least_once()
    {
        $parser = many1(character('p'));
        $expected = failure('Expected character \'p\'');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function sequence_parses_a_sequence_of_parsers()
    {
        $parser = sequence(string('aap'), character(' '));
        $expected = success(
            array('aap', ' '),
            'noot mies'
        );

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function sequence_fails_when_the_sequence_cant_be_parsed()
    {
        $parser = sequence(string('aap'), character('a'), character(' '));
        $expected = failure('Expected character \'a\'');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function choice_parses_the_first_matching_parser()
    {
        $parser = choice(string('foo'), string('aa'), string('aap'));
        $expected = success('aa', 'p noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function choice_fails_when_all_of_its_parsers_fail()
    {
        $parser = choice(string('foo'), string('bar'), string('baz'));
        $expected = failure('Did not match any of the given choices');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function left_parses_two_parsers_and_takes_the_result_of_the_first()
    {
        $parser = left(string('aap'), character(' '));
        $expected = success('aap', 'noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function left_fails_when_one_or_more_of_its_parsers_fail()
    {
        $parser = left(string('aap'), character(' '));

        $expected = failure('Expected string \'aap\'');
        $this->assertEquals(
            $expected,
            parse($parser, 'foo noot mies')
        );

        $expected = failure('Expected character \' \'');
        $this->assertEquals(
            $expected,
            parse($parser, 'aapnoot mies')
        );
    }

    /**
     * @test
     */
    public function right_parses_two_parsers_and_takes_the_result_of_the_second()
    {
        $parser = right(string('aap'), character(' '));
        $expected = success(' ', 'noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function right_fails_when_one_or_more_of_its_parsers_fail()
    {
        $parser = right(string('aap'), character(' '));

        $expected = failure('Expected string \'aap\'');
        $this->assertEquals(
            $expected,
            parse($parser, 'foo noot mies')
        );

        $expected = failure('Expected character \' \'');
        $this->assertEquals(
            $expected,
            parse($parser, 'aapnoot mies')
        );
    }

    /**
     * @test
     */
    public function surroundedBy_parses_surrounded_characters()
    {
        $parser = surroundedBy('[', ']');

        $expected = success('aap', ' noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, '[aap] noot mies')
        );

        $parser = surroundedBy('[[', ']]');

        $expected = success('aap', ' noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, '[[aap]] noot mies')
        );

        $expected = failure('Expected string \'[[\'');
        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function oneOf_parses_a_character_from_a_list_of_characters()
    {
        $parser = oneOf(array('c', 'b', 'a'));
        $expected = success('a', 'ap noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function oneOf_fails_when_none_of_its_characters_can_be_parsed()
    {
        $parser = oneOf(array('f', 'b', 'z'));
        $expected = failure('Character could not be matched');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function noneOf_parses_all_characters_except_a_list_of_characters()
    {
        $parser = noneOf(array(' ', 'p', 'f'));
        $expected = success('a', 'ap noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function noneOf_fails_when_one_of_its_characters_can_be_parsed()
    {
        $parser = noneOf(array('f', 'a', 'z'));
        $expected = failure('Parser was not supposed to match');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function not_inverts_a_given_parser()
    {
        $parser = not(character('p'));
        $expected = success('a', 'ap noot mies');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );

        $parser = not(character('a'));
        $expected = failure('Parser was not supposed to match');

        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function whitespace_parses_spaces_and_tabs()
    {
        $parser = whitespace();

        $expected = success(' ', 'aap noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, ' aap noot mies')
        );

        $expected = success("\t", 'aap noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, "\taap noot mies")
        );

        $expected = failure('Did not match any of the given choices');
        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function eol_parses_an_end_of_line()
    {
        $parser = eol();

        $expected = success("\n", 'aap noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, "\naap noot mies")
        );

        $expected = success("\r\n", 'aap noot mies');
        $this->assertEquals(
            $expected,
            parse($parser, "\r\naap noot mies")
        );

        $expected = failure('Did not match any of the given choices');
        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );
    }

    /**
     * @test
     */
    public function line_parses_a_non_empty_line()
    {
        $parser = line();

        $expected = success('aap noot mies', '');
        $this->assertEquals(
            $expected,
            parse($parser, 'aap noot mies')
        );

        $expected = success('aap noot', 'mies');
        $this->assertEquals(
            $expected,
            parse($parser, "aap noot\nmies")
        );

        $expected = failure('Parser was not supposed to match');
        $this->assertEquals(
            $expected,
            parse($parser, "\naap noot mies")
        );
    }

    /**
     * @test
     */
    public function fmap_applies_a_function_on_a_successful_parers_result()
    {
        $parser = many(character('a'));
        $fmappedParser = fmap($parser, 'implode');

        $expected = success('aa', 'p noot mies');
        $this->assertEquals(
            $expected,
            parse($fmappedParser, 'aap noot mies')
        );
    }
}
