<?php

use PHPUnit\Framework\TestCase;

/**
 * Test Base Processor class
 *
 * @group unit
 */
class CRM_RcBase_Processor_BaseTest extends TestCase
{
    /**
     * Content types
     *
     * @return \string[][]
     */
    public function provideContentTypes()
    {
        return [
            'json' => ['application/json', 'CRM_RcBase_Processor_JSON'],
            'json with whitespace' => [' application/json ', 'CRM_RcBase_Processor_JSON'],
            'json with charset' => ['application/json;charset=UTF-8', 'CRM_RcBase_Processor_JSON'],
            'json with charset and whitespace' => ["\tapplication/json  ;\t\tcharset=UTF-8  ", 'CRM_RcBase_Processor_JSON'],
            'javascript' => ['application/javascript', 'CRM_RcBase_Processor_JSON'],
            'text/xml' => ['text/xml', 'CRM_RcBase_Processor_XML'],
            'application/xml' => ['application/xml', 'CRM_RcBase_Processor_XML'],
            'x-www-form-urlencoded' => ['application/x-www-form-urlencoded', 'CRM_RcBase_Processor_UrlEncodedForm'],
            'text/html fallback to default' => ['text/html', 'CRM_RcBase_Processor_UrlEncodedForm'],
            'random string' => ['something/other/string', 'CRM_RcBase_Processor_UrlEncodedForm'],
        ];
    }

    /**
     * Detect content-type.
     * If not set, it returns default.
     * If set with handled value it returns the relevant class string.
     * If set with unknown value, it returns default.
     *
     * @dataProvider provideContentTypes
     */
    public function testDetectContentType($header, $expected)
    {
        $_SERVER['CONTENT_TYPE'] = $header;
        $result = CRM_RcBase_Processor_Base::detectContentType();
        self::assertEquals($expected, $result, 'Invalid class returned.');
    }

    /**
     * Detect content-type.
     * If not set, it returns default.
     */
    public function testDetectContentTypeWithNoHeadersSet()
    {
        // not set
        unset($_SERVER['CONTENT_TYPE']);
        $result = CRM_RcBase_Processor_Base::detectContentType();
        self::assertEquals('CRM_RcBase_Processor_UrlEncodedForm', $result, 'Invalid class returned.');
    }

    /**
     * Strings to sanitize
     *
     * @return array
     */
    public function provideStringsToSanitize()
    {
        return [
            'Empty string' => ['', ''],
            'No change' => ['this_is_kept_as_it_is', 'this_is_kept_as_it_is'],
            'Trailing newline' => ["first word\n", 'first word'],
            'Middle newline' => ["first\nword", "first\nword"],
            'Trailing whitespace' => ["first word  \t\n", 'first word'],
            'Leading whitespace' => ["\n  \tfirst word", 'first word'],
            'Double whitespace' => ["first   word\tand\t\tsecond word", "first word\tand second word"],
            'Double quotes around' => ["\"first_and_last_removed\"", 'first_and_last_removed'],
            'Single quotes around' => ["'first_and_last_also_removed'", 'first_and_last_also_removed'],
            'Quotes inside' => ["'middle_\"one'_is_kept'", "middle_\"one'_is_kept"],
            'Tags' => [
                "without_html_<a href=\"site.com\" target=\"_blank\">link</a>_tags",
                'without_html_link_tags',
            ],
        ];
    }

    /**
     * @dataProvider provideStringsToSanitize
     *
     * @param $input
     * @param $expected
     */
    public function testSanitizeString($input, $expected)
    {
        $result = CRM_RcBase_Processor_Base::sanitizeString($input);
        self::assertEquals($expected, $result, 'Invalid sanitized string returned.');
    }

    /**
     * Basic input to sanitize
     */
    public function provideBasicTypeToSanitize()
    {
        return [
            'string' => ["\n  \t'first'<alert> word'  ", "first' word"],
            'positive integer' => [3, 3],
            'zero integer' => [0, 0],
            'negative integer' => [-1, -1],
            'positive float' => [3.3, 3.3],
            'zero float' => [0.0, 0.0],
            'negative float' => [-1.9, -1.9],
            'bool true' => [true, true],
            'bool false' => [false, false],
            'null' => [null, null],
            'empty array' => [[], null],
            'empty string' => ['', ''],
        ];
    }

    /**
     * @dataProvider provideBasicTypeToSanitize
     *
     * @param $input
     * @param $expected
     */
    public function testSanitizeWithBasicInputTypes($input, $expected)
    {
        $result = CRM_RcBase_Processor_Base::sanitize($input);
        self::assertEquals($expected, $result, 'Invalid sanitized value returned.');
    }

    /**
     * Test sanitize with a real world looking array
     */
    public function testSanitizeWithComplexArray()
    {
        $input = [
            "  <script>alert(hack)\t</script>\n" => 'this is a test',
            'sub_array' => [
                'first ',
                'se   cond',
                15,
                true,
                null,
                -45.431,
                [1, 2, 3],
                [
                    're   curse <html>' => '"index" ',
                    'integer' => 5,
                    'boolean' => true,
                    're-recurse' => [
                        'greater_than' => '>15',
                        'less then' => '>11',
                        'tags' => '<input/>testing<br/>',
                        5 => null,
                        6 => ' six',
                    ],
                ],
            ],
            'email ' => "test@example.com\n",
            'UTF-8' => 'kéményŐÜÖÓúőü$!#~`\\|',
        ];
        $expected = [
            "alert(hack)\t" => 'this is a test',
            'sub_array' => [
                'first',
                'se cond',
                15,
                true,
                null,
                -45.431,
                [1, 2, 3],
                [
                    're curse ' => 'index',
                    'integer' => 5,
                    'boolean' => true,
                    're-recurse' => [
                        'greater_than' => '>15',
                        'less then' => '>11',
                        'tags' => 'testing',
                        5 => null,
                        6 => 'six',
                    ],
                ],
            ],
            'email' => 'test@example.com',
            'UTF-8' => 'kéményŐÜÖÓúőü$!#~`\\|',
        ];
        $result = CRM_RcBase_Processor_Base::sanitize($input);
        self::assertEquals($expected, $result, 'Invalid sanitized array returned.');
    }

    public function testValidateInputMissingTypeThrowsException()
    {
        $value = 'testvalue';
        $type = '';
        $name = 'testname';
        $required = false;
        $allowedValues = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Variable type missing');
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputMissingNameThrowsException()
    {
        $value = 'testvalue';
        $type = 'testtype';
        $name = '';
        $required = false;
        $allowedValues = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Variable name missing');
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputEmptyRequiredValueThrowsException()
    {
        $value = '';
        $type = 'testtype';
        $name = 'testname';
        $required = true;
        $allowedValues = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing parameter: '.$name);
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputNotSupportedTypeThrowsException()
    {
        $value = 'testvalue';
        $type = 'invalidTypeName';
        $name = 'testname';
        $required = false;
        $allowedValues = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Not supported type: '.$type);
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputNotAllowedValueThrowsException()
    {
        $value = 'invalid value';
        $type = 'string';
        $name = 'testname';
        $required = false;
        $allowedValues = ['ON', 'OFF'];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Not allowed value for: '.$name.' ('.$value.')');
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateStringWithArrayThrowsException()
    {
        $value = ['test'];
        $type = 'string';
        $name = 'array instead string';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingLocalPartThrowsException()
    {
        $value = '@example.com';
        $type = 'email';
        $name = 'missing local part';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingDomainThrowsException()
    {
        $value = 'test@';
        $type = 'email';
        $name = 'missing domain';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingTopLevelDomainThrowsException()
    {
        $value = 'test@example';
        $type = 'email';
        $name = 'missing top level domain';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateIntegerWithStringThrowsException()
    {
        $value = 'string';
        $type = 'int';
        $name = 'string';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateFloatWithStringThrowsException()
    {
        $value = 'string';
        $type = 'float';
        $name = 'string';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateBoolWithStringThrowsException()
    {
        $value = 'string';
        $type = 'bool';
        $name = 'string';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateWithTimeThrowsException()
    {
        $value = '202004021531';
        $type = 'date';
        $name = 'date with time';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithMissingDayThrowsException()
    {
        $value = '202011';
        $type = 'datetime';
        $name = 'missing day';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithMissingDayMonthThrowsException()
    {
        $value = '2020';
        $type = 'datetime';
        $name = 'missing day month';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithRandomStringThrowsException()
    {
        $value = 'random';
        $type = 'datetime';
        $name = 'random string';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeIsoWithMissingDecimalsThrowsException()
    {
        $value = '2020-12-01T03:19:46+04:30';
        $type = 'datetimeIso';
        $name = 'missing seconds decimals';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeIsoWithTooMuchDecimalsThrowsException()
    {
        $value = '2020-12-01T03:19:46.1234567+04:30';
        $type = 'datetimeIso';
        $name = 'more than 6 seconds decimals';

        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage("${name} is not type of: ${type}");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function provideValidInput()
    {
        return [
            'empty string' => ['', 'string', 'empty string', false, [],],
            'no allowed specified' => ['any string', 'string', 'no allowed specified', false, [],],
            'required' => ['any string', 'string', 'required', true, [],],
            'valid and allowed' => [
                'valid string value 1',
                'string',
                'valid and allowed',
                false,
                ['valid string value 1', 'valid string value 2'],
            ],
            'valid allowed and required' => [
                'valid string value 2',
                'string',
                'valid allowed and required',
                true,
                ['valid string value 1', 'valid string value 2'],
            ],
            'email address' => ['email@addr.hu', 'email', 'email address', false, []],
            'integer as int' => [87, 'int', 'integer as int', false, [12, '87']],
            'integer as string' => ['12', 'int', 'integer as string', false, [12, '87'],],
            'negative integer as int' => [-12, 'int', 'negative integer as int', false, [],],
            'negative integer as string' => ['-12', 'int', 'negative integer as string', false, [],],
            'ID as int' => [12, 'id', 'ID as int', false, [],],
            'ID as string' => ['12', 'id', 'ID as string', false, [],],
            'float as float' => [12.1, 'float', 'float as float', false, [],],
            'float as string' => ['12.1', 'float', 'float as string', false, [],],
            'bool as bool' => [false, 'bool', 'bool as bool', false, [],],
            'bool as truthy int' => [1, 'bool', 'bool as truthy int', false, [],],
            'bool as falsy int' => [0, 'bool', 'bool as falsy int', false, [],],
            'bool as truthy string' => ['Y', 'bool', 'bool as truthy string', false, [],],
            'bool as falsy string' => ['no', 'bool', 'bool as falsy string', false, [],],
            'date full' => ['2020-08-13', 'date', 'date full', false, [],],
            'date compact' => ['20200813', 'date', 'date compact', false, [],],
            'datetime' => ['2020-11-27 04:52:28', 'datetime', 'datetime full', false, [],],
            'datetime only date' => ['2020-11-27', 'datetime', 'datetime only date', false, [],],
            'datetime compact' => ['20201127045228', 'datetime', 'datetime compact', false, [],],
            'datetime no seconds' => ['2020-11-27 04:52', 'datetime', 'datetime no seconds', false, [],],
            'datetime no seconds compact' => [
                '202011270452',
                'datetime',
                'datetime no seconds compact',
                false,
                [],
            ],
            'datetime ISO' => [
                '2020-12-01T03:19:46.101Z',
                'datetimeIso',
                'datetime ISO',
                false,
                [],
            ],
            'datetime ISO plus timezone' => [
                '2020-12-01T03:19:46.101+02:00',
                'datetimeIso',
                'datetime ISO plus timezone',
                false,
                [],
            ],
            'datetime ISO minus timezone' => [
                '2020-12-01T03:19:46.101-05:30',
                'datetimeIso',
                'datetime ISO minus timezone',
                false,
                [],
            ],
            'datetime ISO 1 digits microseconds' => [
                '2020-12-01T03:19:46.1Z',
                'datetimeIso',
                'datetime ISO 2 digits microseconds',
                false,
                [],
            ],
            'datetime ISO 3 digits microseconds' => [
                '2020-12-01T03:19:46.123Z',
                'datetimeIso',
                'datetime ISO 3 digits microseconds',
                false,
                [],
            ],
            'datetime ISO 6 digits microseconds' => [
                '2020-12-01T03:19:46.123456Z',
                'datetimeIso',
                'datetime ISO 6 digits microseconds',
                false,
                [],
            ],
        ];
    }

    /**
     * @dataProvider provideValidInput
     *
     * @param $value
     * @param $type
     * @param $name
     * @param $required
     * @param $allowedValues
     */
    public function testValidateInputValidValues($value, $type, $name, $required, $allowedValues)
    {
        try {
            self::assertEmpty(
                CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues),
                'Should be empty for valid setup.'
            );
        } catch (Exception $ex) {
            self::fail('Should not throw exception for valid setup. '.$ex->getMessage());
        }
    }
}
