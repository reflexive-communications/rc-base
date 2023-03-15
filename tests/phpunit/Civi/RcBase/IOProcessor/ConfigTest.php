<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Exception\InvalidArgumentException;

/**
 * Test Config Processor class
 *
 * @group headless
 */
class ConfigTest extends HeadlessTestCase
{
    /**
     * @return array[]
     */
    public function provideStrings(): array
    {
        return [
            'null' => ['option=null', ['option' => null]],
            'empty string' => ['option=', ['option' => '']],
            'string' => ['option=string', ['option' => 'string']],
            'integer' => ['integer=42', ['integer' => 42]],
            'float' => ['float=7.523', ['float' => 7.523]],
            'int 1' => ['bool_option=1', ['bool_option' => 1]],
            'int 0' => ['bool_option=0', ['bool_option' => 0]],
            'true' => ['bool_option=true', ['bool_option' => true]],
            'yes' => ['bool_option=yes', ['bool_option' => true]],
            'on' => ['bool_option=on', ['bool_option' => true]],
            'false' => ['bool_option=false', ['bool_option' => false]],
            'no' => ['bool_option=no', ['bool_option' => false]],
            'off' => ['bool_option=off', ['bool_option' => false]],
            'whitespace' => [
                "whitespace= \tlong string\t\t  \tparts\t  ",
                ['whitespace' => 'long string parts'],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function provideHeaders(): array
    {
        return [
            'ini+headers, process headers' => [
                "[Main]\noption_1=string\noption_2=176\n[Other config]\ndebug=off",
                true,
                [
                    'Main' => [
                        'option_1' => 'string',
                        'option_2' => 176,
                    ],
                    'Other config' => [
                        'debug' => false,
                    ],
                ],
            ],
            'ini+headers, but dont process headers' => [
                "[Main]\noption_1=string\noption_2=176\n[Other config]\ndebug=off",
                false,
                [
                    'option_1' => 'string',
                    'option_2' => 176,
                    'debug' => false,
                ],
            ],
            'no headers but process headers' => [
                "option_1=string\noption_2=176\ndebug=off",
                true,
                [
                    'option_1' => 'string',
                    'option_2' => 176,
                    'debug' => false,
                ],
            ],
            'no headers and dont process headers' => [
                "option_1=string\noption_2=176\ndebug=off",
                false,
                [
                    'option_1' => 'string',
                    'option_2' => 176,
                    'debug' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideStrings
     *
     * @param $ini_string
     * @param $expected
     *
     * @throws \CRM_Core_Exception
     */
    public function testParseIniString($ini_string, $expected)
    {
        $result = Config::parseIniString($ini_string);
        self::assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @dataProvider provideHeaders
     *
     * @param $ini_string
     * @param $process_sections
     * @param $expected
     *
     * @throws \CRM_Core_Exception
     */
    public function testParsingHeaders($ini_string, $process_sections, $expected)
    {
        $result = Config::parseIniString($ini_string, $process_sections);
        self::assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testParseInvalidStringThrowsException()
    {
        $ini_string = 'options=off second=no newline';
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('ini string');
        Config::parseIniString($ini_string);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testNormalScanningMode()
    {
        $ini_string = 'bool=true';
        $expected = ['bool' => '1'];
        $result = Config::parseIniString($ini_string, true, INI_SCANNER_NORMAL);
        self::assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testTypedScanningMode()
    {
        $ini_string = 'bool=on';
        $expected = ['bool' => true];
        $result = Config::parseIniString($ini_string, true, INI_SCANNER_TYPED);
        self::assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testParseIniFile()
    {
        $filename = __DIR__.'/test.ini';
        $expected = [
            'Main' => [
                'option_1' => 'string',
                'option_2' => 176,
            ],
            'Other config' => [
                'debug' => false,
            ],
        ];
        $result = Config::parseIniFile($filename);
        self::assertSame($expected, $result, 'Failed to parse ini file');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testParseIniFileWithComments()
    {
        $filename = __DIR__.'/comment.ini';
        $expected = [
            'Main' => [
                'option_1' => 'string',
            ],
            'Other config' => [
                'debug' => false,
            ],
        ];
        $result = Config::parseIniFile($filename);
        self::assertSame($expected, $result, 'Failed to parse ini file');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testMissingIniFileThrowsException()
    {
        $filename = __DIR__.'/non-existent.ini';
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Failed to parse ini file');
        Config::parseIniFile($filename);
    }

    /**
     * @return void
     */
    public function testIniStringFromDictionary()
    {
        $dictionary = [
            'ping' => 'pong',
            'bool-true' => true,
            'bool-false' => false,
            'integer' => 5,
            'null' => null,
            1 => '6',
            'array' => [1, 2],
        ];
        $ini_string = "ping=pong\nbool-true=true\nbool-false=false\ninteger=5\nnull=null\n1=6";
        self::assertSame($ini_string, Config::iniStringFromDictionary($dictionary), 'Wrong INI string returned');
    }
}
