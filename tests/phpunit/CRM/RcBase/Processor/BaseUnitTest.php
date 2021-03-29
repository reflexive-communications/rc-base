<?php

/**
 * Test Base Processor class
 *
 * @group unit
 */
class CRM_RcBase_Processor_BaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Detect content-type.
     * If not set, it returns default.
     * If set with handled value it returns the relevant class string.
     * If set with unknown value, it returns default.
     */
    public function testDetectContentType()
    {
        // not set.
        $result = CRM_RcBase_Processor_Base::detectContentType();
        $this->assertEquals("CRM_RcBase_Processor_UrlEncodedForm", $result, "Invalid class returned.");
        $testData = [
            "application/json" => "CRM_RcBase_Processor_JSON",
            "application/javascript" => "CRM_RcBase_Processor_JSON",
            "text/xml" => "CRM_RcBase_Processor_XML",
            "application/xml" => "CRM_RcBase_Processor_XML",
            "text/html" => "CRM_RcBase_Processor_UrlEncodedForm",
            "something/other/string" => "CRM_RcBase_Processor_UrlEncodedForm",
        ];
        foreach ($testData as $k => $v) {
            $_SERVER["CONTENT_TYPE"] = $k;
            $result = CRM_RcBase_Processor_Base::detectContentType();
            $this->assertEquals($v, $result, "Invalid class returned.");
        }
    }

    /**
     * Strings to sanitize
     *
     * @return array
     */
    public function provideStringsToSanitize()
    {
        return [
            'Empty string' => ["", ""],
            'No change' => ["this_is_kept_as_it_is", "this_is_kept_as_it_is"],
            'Trailing newline' => ["first word\n", "first word"],
            'Middle newline' => ["first\nword", "first\nword"],
            'Trailing whitespace' => ["first word  \t\n", "first word"],
            'Leading whitespace' => ["\n  \tfirst word", "first word"],
            'Double whitespace' => ["first   word\tand\t\tsecond word", "first word\tand second word"],
            'Double quotes around' => ["\"first_and_last_removed\"", "first_and_last_removed"],
            'Single quotes around' => ["'first_and_last_also_removed'", "first_and_last_also_removed"],
            'Quotes inside' => ["'middle_\"one'_is_kept'", "middle_\"one'_is_kept"],
            'Tags' => ["without_html_<a href=\"site.com\" target=\"_blank\">link</a>_tags", "without_html_link_tags"],
        ];
    }

    /**
     * @dataProvider provideStringsToSanitize
     * @param $input
     * @param $expected
     */
    public function testSanitizeString($input, $expected)
    {
        $result = CRM_RcBase_Processor_Base::sanitizeString($input);
        $this->assertEquals($expected, $result, "Invalid sanitized string returned.");
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
            'empty string' => ["", ""],
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
        $this->assertEquals($expected, $result, "Invalid sanitized value returned.");
    }

    /**
     * Test sanitize with a real world looking array
     */
    public function testSanitizeWithComplexArray()
    {
        $input = [
            "  <script>alert(hack)\t</script>\n" => "this is a test",
            'sub_array' => [
                "first ",
                "se   cond",
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
            "alert(hack)\t" => "this is a test",
            'sub_array' => [
                "first",
                "se cond",
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
            'email' => "test@example.com",
            'UTF-8' => 'kéményŐÜÖÓúőü$!#~`\\|',
        ];
        $result = CRM_RcBase_Processor_Base::sanitize($input);
        $this->assertEquals($expected, $result, "Invalid sanitized array returned.");
    }

    public function testValidateInputMissingTypeThrowsException()
    {
        $value = "testvalue";
        $type = "";
        $name = "testname";
        $required = false;
        $allowedValues = [];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Variable type missing", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputMissingNameThrowsException()
    {
        $value = "testvalue";
        $type = "testtype";
        $name = "";
        $required = false;
        $allowedValues = [];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Variable name missing", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputEmptyRequiredValueThrowsException()
    {
        $value = "";
        $type = "testtype";
        $name = "testname";
        $required = true;
        $allowedValues = [];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Missing parameter: ".$name, "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputNotSupportedTypeThrowsException()
    {
        $value = "testvalue";
        $type = "invalidTypeName";
        $name = "testname";
        $required = false;
        $allowedValues = [];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Not supported type: ".$type, "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputNotAllowedValueThrowsException()
    {
        $value = "invalid value";
        $type = "string";
        $name = "testname";
        $required = false;
        $allowedValues = ["ON", "OFF"];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Not allowed value for: ".$name." (".$value.")", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateStringWithArrayThrowsException()
    {
        $value = ['test'];
        $type = "string";
        $name = "array instead string";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingLocalPartThrowsException()
    {
        $value = '@example.com';
        $type = "email";
        $name = "missing local part";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingDomainThrowsException()
    {
        $value = 'test@';
        $type = "email";
        $name = "missing domain";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateEmailWithMissingTopLevelDomainThrowsException()
    {
        $value = 'test@example';
        $type = "email";
        $name = "missing top level domain";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateIntegerWithStringThrowsException()
    {
        $value = 'string';
        $type = "int";
        $name = "string";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateFloatWithStringThrowsException()
    {
        $value = 'string';
        $type = "float";
        $name = "string";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateBoolWithStringThrowsException()
    {
        $value = 'string';
        $type = "bool";
        $name = "string";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateWithTimeThrowsException()
    {
        $value = '202004021531';
        $type = "date";
        $name = "date with time";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithMissingDayThrowsException()
    {
        $value = '202011';
        $type = "datetime";
        $name = "missing day";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithMissingDayMonthThrowsException()
    {
        $value = '2020';
        $type = "datetime";
        $name = "missing day month";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeWithRandomStringThrowsException()
    {
        $value = 'random';
        $type = "datetime";
        $name = "random string";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeIsoWithMissingDecimalsThrowsException()
    {
        $value = "2020-12-01T03:19:46+04:30";
        $type = "datetimeIso";
        $name = "missing seconds decimals";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateDateTimeIsoWithTooMuchDecimalsThrowsException()
    {
        $value = "2020-12-01T03:19:46.1234567+04:30";
        $type = "datetimeIso";
        $name = "more than 6 seconds decimals";

        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("${name} is not type of: ${type}", "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name);
    }

    public function testValidateInputValidValues()
    {
        $testData = [
            [
                "value" => "",
                "type" => "string",
                "name" => "empty string",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "any string",
                "type" => "string",
                "name" => "no allowed specified",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "valid string value 1",
                "type" => "string",
                "name" => "valid and allowed",
                "required" => false,
                "allowedValues" => ["valid string value 1", "valid string value 2"],
            ],
            [
                "value" => "valid string value 2",
                "type" => "string",
                "name" => "valid allowed and required",
                "required" => true,
                "allowedValues" => ["valid string value 1", "valid string value 2"],
            ],
            [
                "value" => "email@addr.hu",
                "type" => "email",
                "name" => "email address",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 87,
                "type" => "int",
                "name" => "integer as int",
                "required" => false,
                "allowedValues" => [12, "87"],
            ],
            [
                "value" => "12",
                "type" => "int",
                "name" => "integer as string",
                "required" => false,
                "allowedValues" => [12, "87"],
            ],
            [
                "value" => "-12",
                "type" => "int",
                "name" => "negative integer as string",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => -12,
                "type" => "int",
                "name" => "negative integer as int",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 12,
                "type" => "id",
                "name" => "ID",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 12.1,
                "type" => "float",
                "name" => "float as float",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "12.1",
                "type" => "float",
                "name" => "float as string",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => false,
                "type" => "bool",
                "name" => "bool as bool",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 1,
                "type" => "bool",
                "name" => "bool as truthy integer",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 0,
                "type" => "bool",
                "name" => "bool as falsy integer",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "Y",
                "type" => "bool",
                "name" => "bool as truthy string",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "no",
                "type" => "bool",
                "name" => "bool as falsy string",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01",
                "type" => "date",
                "name" => "date full",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "20201201",
                "type" => "date",
                "name" => "date compact",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01 11:22:22",
                "type" => "datetime",
                "name" => "datetime full",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01",
                "type" => "datetime",
                "name" => "datetime only date",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01 11:22:22",
                "type" => "datetime",
                "name" => "datetime compact",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01 11:22",
                "type" => "datetime",
                "name" => "datetime no seconds",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "202012011122",
                "type" => "datetime",
                "name" => "datetime no seconds compact",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.101Z",
                "type" => "datetimeIso",
                "name" => "datetime ISO",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.101+02:00",
                "type" => "datetimeIso",
                "name" => "datetime ISO plus timezone",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.101-05:30",
                "type" => "datetimeIso",
                "name" => "datetime ISO minus timezone",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.1Z",
                "type" => "datetimeIso",
                "name" => "datetime ISO 2 digits microseconds",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.123Z",
                "type" => "datetimeIso",
                "name" => "datetime ISO 3 digits microseconds",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T03:19:46.123456Z",
                "type" => "datetimeIso",
                "name" => "datetime ISO 6 digits microseconds",
                "required" => false,
                "allowedValues" => [],
            ],
        ];
        foreach ($testData as $t) {
            try {
                $this->assertEmpty(
                    CRM_RcBase_Processor_Base::validateInput(
                        $t["value"],
                        $t["type"],
                        $t["name"],
                        $t["required"],
                        $t["allowedValues"]
                    ),
                    "Should be empty for valid setup."
                );
            } catch (Exception $e) {
                $this->fail("Should not throw exception for valid setup. ".$e->getMessage());
            }
        }
    }
}
