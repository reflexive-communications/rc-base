<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class StringsTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testCompactWhitespace()
    {
        $input = " This
        is a test     string\n\twith multiple spaces.  ";
        $expected = 'This is a test string with multiple spaces.';
        self::assertSame($expected, Strings::compactWhitespace($input), 'Wrong compacted string');

        $input = ' not  trimmed  ';
        $expected = ' not trimmed ';
        self::assertSame($expected, Strings::compactWhitespace($input, false), 'Wrong compacted string');
    }

    /**
     * @return void
     */
    public function testRemoveComments()
    {
        $input = "this is a test /* with comment */ string // with comment\nand a new line\n";
        $expected = "this is a test  string \nand a new line\n";
        self::assertSame($expected, Strings::removeComments($input), 'Wrong filtered string');

        $input = '/* comment with @@ */ and unbalanced comment */';
        $expected = ' and unbalanced comment */';
        self::assertSame($expected, Strings::removeComments($input), 'Wrong filtered string');

        self::assertSame('', Strings::removeComments(''), 'Wrong filtered string');
    }
}
