<?php

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_RcBase_Processor_BaseUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * The setup() method is executed before the test is executed (optional).
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * The tearDown() method is executed after the test was executed (optional)
     * This can be used for cleanup.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

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
     * Complex arrays to sanitize
     */
    public function provideArrayToSanitize()
    {
        [
            [
                "input" => [],
                "expected" => null,
            ],
            [
                "input" => ["key" => 3.14],
                "expected" => ["key" => 3.14],
            ],
            [
                "input" => ["'key'" => 3.14],
                "expected" => ["key" => 3.14],
            ],
            [
                "input" => ["'constants'" => ["'pi'" => 3.14]],
                "expected" => ["constants" => ["pi" => 3.14]],
            ],
        ];
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
            'UTF-8' => 'kéményŐÜÖÓúőü',
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
            'UTF-8' => 'kéményŐÜÖÓúőü',
        ];
        $result = CRM_RcBase_Processor_Base::sanitize($input);
        $this->assertEquals($expected, $result, "Invalid sanitized array returned.");
    }

    public function testValidateInputMissingType()
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

    public function testValidateInputMissingName()
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

    public function testValidateInputEmptyRequiredValue()
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

    public function testValidateInputNotSupportedType()
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

    public function testValidateInputInvalidValue()
    {
        $value = "testvalue";
        $type = "email";
        $name = "testname";
        $required = false;
        $allowedValues = [];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage($name." is not type of: ".$type, "Invalid exception message.");
        CRM_RcBase_Processor_Base::validateInput($value, $type, $name, $required, $allowedValues);
    }

    public function testValidateInputNotAllowedValue()
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

    public function testValidateInputValidValues()
    {
        $testData = [
            [
                "value" => "",
                "type" => "string",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "valid string value",
                "type" => "string",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "email@addr.hu",
                "type" => "email",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "12",
                "type" => "int",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "-12",
                "type" => "int",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => -12,
                "type" => "int",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 12,
                "type" => "id",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => 12.1,
                "type" => "float",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => false,
                "type" => "bool",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01",
                "type" => "date",
                "name" => "testName",
                "required" => false,
                "allowedValues" => [],
            ],
            [
                "value" => "2020-12-01T11:22:22.101Z",
                "type" => "datetime",
                "name" => "testName",
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
                $this->fail("Should not throw exception for valid setup.".$e->getMessage());
            }
        }
    }
}
