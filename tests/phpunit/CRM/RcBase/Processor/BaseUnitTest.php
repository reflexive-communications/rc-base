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
            'No change' => ["this_is_kept_as_it_is", "this_is_kept_as_it_is"],
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

    public function testSanitize()
    {
        $testDataBasic = [
            "this_is_kept_as_it_is" => "this_is_kept_as_it_is",
            "\"first_and_last_removed\"" => "first_and_last_removed",
            "'first_and_last_also_removed'" => "first_and_last_also_removed",
            "'middle_one'_is_kept'" => "middle_one'_is_kept",
            "without_html_<a href=\"site.com\" target=\"_blank\">link</a>_tags" => "without_html_link_tags",
            3 => 3,
            0 => 0,
            -1 => -1,
            1.1 => 1.1,
            -0.3 => -0.3,
            true => true,
            false => false,
        ];
        $testDataArray = [
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
        $stub = $this->getMockForAbstractClass('CRM_RcBase_Processor_Base');
        foreach ($testDataBasic as $k => $v) {
            $result = $stub->sanitize($k);
            $this->assertEquals($v, $result, "Invalid sanitized value returned.");
        }
        foreach ($testDataArray as $v) {
            $result = $stub->sanitize($v["input"]);
            $this->assertEquals($v["expected"], $result, "Invalid sanitized object returned.");
        }
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
