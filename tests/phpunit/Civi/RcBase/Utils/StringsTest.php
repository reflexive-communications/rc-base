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
}
