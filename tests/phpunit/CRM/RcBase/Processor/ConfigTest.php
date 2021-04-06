<?php

use PHPUnit\Framework\TestCase;

/**
 * Test Config Processor class
 *
 * @group unit
 */
class CRM_RcBase_Processor_ConfigTest extends TestCase
{
    public function provideStrings()
    {
        return [
            'null' => ['option=null', ['option' => null,]],
            'empty string' => ['option=', ['option' => '',]],
            'string' => ['option=string', ['option' => 'string',]],
            'integer' => ['integer=42', ['integer' => 42,]],
            'float' => ['float=7.523', ['float' => 7.523,]],
            'int 1' => ['bool_option=1', ['bool_option' => 1,]],
            'int 0' => ['bool_option=0', ['bool_option' => 0,]],
            'true' => ['bool_option=true', ['bool_option' => true,]],
            'yes' => ['bool_option=yes', ['bool_option' => true,]],
            'on' => ['bool_option=on', ['bool_option' => true,]],
            'false' => ['bool_option=false', ['bool_option' => false,]],
            'no' => ['bool_option=no', ['bool_option' => false,]],
            'off' => ['bool_option=off', ['bool_option' => false,]],
            'whitespace' => [
                "whitespace= \tlong string\t\t  \tparts\t  ",
                ['whitespace' => 'long string parts',],
            ],
        ];
    }

    public function provideHeaders()
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
        $result = CRM_RcBase_Processor_Config::parseIniString($ini_string);
        $this->assertSame($expected, $result, 'Failed to parse ini string');
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
        $result = CRM_RcBase_Processor_Config::parseIniString($ini_string, $process_sections);
        $this->assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testParseInvalidStringThrowsException()
    {
        $ini_string = 'options=off second=no newline';
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class.');
        $this->expectExceptionMessage('Failed to parse ini string', 'Invalid exception message.');
        CRM_RcBase_Processor_Config::parseIniString($ini_string);
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testNormalScanningMode()
    {
        $ini_string = 'bool=true';
        $expected = ['bool' => '1',];
        $result = CRM_RcBase_Processor_Config::parseIniString($ini_string, true, INI_SCANNER_NORMAL);
        $this->assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testTypedScanningMode()
    {
        $ini_string = 'bool=on';
        $expected = ['bool' => true,];
        $result = CRM_RcBase_Processor_Config::parseIniString($ini_string, true, INI_SCANNER_TYPED);
        $this->assertSame($expected, $result, 'Failed to parse ini string');
    }

    /**
     * @throws \CRM_Core_Exception
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
        $result = CRM_RcBase_Processor_Config::parseIniFile($filename);
        $this->assertSame($expected, $result, 'Failed to parse ini file');
    }

    /**
     * @throws \CRM_Core_Exception
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
        $result = CRM_RcBase_Processor_Config::parseIniFile($filename);
        $this->assertSame($expected, $result, 'Failed to parse ini file');
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testMissingIniFileThrowsException()
    {
        $filename = __DIR__.'/non-existent.ini';
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class.');
        $this->expectExceptionMessage('Failed to parse ini file', 'Invalid exception message.');
        CRM_RcBase_Processor_Config::parseIniFile($filename);
    }
}
