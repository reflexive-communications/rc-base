<?php

/**
 * Test JSON Processor class
 *
 * @group unit
 */
class CRM_RcBase_Processor_JSONTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provide valid JSON
     *
     * @return array
     */
    public function provideValidJson()
    {
        return [
            'empty string' => ['""', ''],
            'empty object' => ['{}', null],
            'empty array' => ['[]', null],
            'null' => ['null', null],
            'string' => ['"some string"', 'some string'],
            'integer' => ['5', 5],
            'float' => ['-21.984', -21.984],
            'bool' => ['false', false],
            'array' => ['["string 1","string 2"]', ['string 1', 'string 2']],
            'complex' => [
                '{"0":"string","1":"5","2":5,"3":-5,"4":1.1,"5":true,"field_1":"value_2","field_2":"value_2","6":["a","b","c"]}',
                ['string', '5', 5, -5, 1.1, true, 'field_1' => 'value_2', 'field_2' => 'value_2', ['a', 'b', 'c']],
            ],
            'need to sanitize' => [
                '{"field\t\t_1  ":"<script>value</script>","   field_2":"value_2\n\n"}',
                [
                    'field _1' => 'value',
                    'field_2' => 'value_2',
                ],
            ],
            'whitespace' => [
                '{"\n   field_1   "  :  "\t\t\tvalue_1","field_2":"value_2"}',
                [
                    'field_1' => 'value_1',
                    'field_2' => 'value_2',
                ],
            ],
            'UTF-8' => [
                '{"ÖÜÓŐÚÉÁŰÍ": "öüóőúéáűí"}',
                ['ÖÜÓŐÚÉÁŰÍ' => 'öüóőúéáűí'],
            ],
        ];

    }

    /**
     * Test valid JSON
     *
     * @dataProvider provideValidJson
     *
     * @param $json
     * @param $object
     * @throws CRM_Core_Exception
     */
    public function testValidJson($json, $object)
    {
        $result = CRM_RcBase_Processor_JSON::input($json);
        $this->assertSame($object, $result, 'Invalid JSON returned.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testInvalidJsonThrowsException()
    {
        $json = '["string 1"=,"string 2"]';
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class.");
        $this->expectExceptionMessage("Invalid JSON received", "Invalid exception message.");
        CRM_RcBase_Processor_JSON::input($json);
    }

    public function testReadFromDataSocket()
    {
        $base64_enc = "eyLDlsOcw5PFkMOaw4nDgcWww40iOiAiw7bDvMOzxZHDusOpw6HFscOtIn0K";
        $expected = ['ÖÜÓŐÚÉÁŰÍ' => 'öüóőúéáűí'];
        $result = CRM_RcBase_Processor_JSON::inputStream('data://text/plain;base64,'.$base64_enc);
        $this->assertSame($expected, $result, 'Invalid JSON returned.');
    }

    public function testReadFromFileSocket()
    {
        $expected = [
            'string' => 'some string',
            1 => '5',
            2 => 5,
            3 => -5,
            4 => 1.1,
            5 => true,
            'field_1' => 'value_2',
            'field_2' => 'value_2',
            6 =>
                [
                    0 => 'a',
                    1 => 'b',
                    2 => 'c',
                ],
        ];
        $result = CRM_RcBase_Processor_JSON::inputStream('file://'.__DIR__.'/test.json');
        $this->assertSame($expected, $result, 'Invalid JSON returned.');
    }
}
