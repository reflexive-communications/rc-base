<?php

/**
 * @group headless
 */
class CRM_RcBase_Processor_JSONTest extends CRM_RcBase_HeadlessTestCase
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
                '{"0":"string","1":"5","2":5,"3":-5,"4":1.1,"5":true,"field_1":"value_2","field_2":"value_2","6":["a","b","c"],"utf-8":"éáÜŐ"}',
                [
                    'string',
                    '5',
                    5,
                    -5,
                    1.1,
                    true,
                    'field_1' => 'value_2',
                    'field_2' => 'value_2',
                    ['a', 'b', 'c'],
                    'utf-8' => 'éáÜŐ',
                ],
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
     * @dataProvider provideValidJson
     *
     * @param $json
     * @param $object
     *
     * @throws CRM_Core_Exception
     */
    public function testValidJson($json, $object)
    {
        $result = CRM_RcBase_Processor_JSON::parse($json);
        self::assertSame($object, $result, 'Invalid JSON returned.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testInvalidJsonThrowsException()
    {
        $json = '["string 1"=,"string 2"]';
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid JSON received');
        CRM_RcBase_Processor_JSON::parse($json);
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testParseDataStream()
    {
        $base64_enc = 'eyLDlsOcw5PFkMOaw4nDgcWww40iOiAiw7bDvMOzxZHDusOpw6HFscOtIn0K';
        $expected = ['ÖÜÓŐÚÉÁŰÍ' => 'öüóőúéáűí'];
        $result = CRM_RcBase_Processor_JSON::parseStream('data://text/plain;base64,'.$base64_enc);
        self::assertSame($expected, $result, 'Invalid JSON returned.');
    }

    /**
     * @throws \CRM_Core_Exception
     */
    public function testParseFileStream()
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
        $result = CRM_RcBase_Processor_JSON::parseStream('file://'.__DIR__.'/test.json');
        self::assertSame($expected, $result, 'Invalid JSON returned.');
    }

    public function testFailedParseStreamThrowsException()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Failed to open stream');
        CRM_RcBase_Processor_JSON::parseStream('file://'.__DIR__.'/non-existent.json');
    }

    public function testEncode()
    {
        $data = [
            'string',
            '5',
            5,
            -5,
            1.1,
            true,
            'field_1' => 'value_2',
            'field_2' => 'value_2',
            ['a', 'b', 'c'],
            'ÖÜÓŐÚÉÁŰÍ' => 'öüóőúéáűí',
        ];
        $json
            = '{"0":"string","1":"5","2":5,"3":-5,"4":1.1,"5":true,"field_1":"value_2","field_2":"value_2","6":["a","b","c"],"ÖÜÓŐÚÉÁŰÍ":"öüóőúéáűí"}';

        $result = CRM_RcBase_Processor_JSON::encode($data);
        self::assertSame($json, $result, 'Invalid JSON returned.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testParsePost()
    {
        $json = [
            '{"0":"string","1":"5","2":5,"3":-5,"4":1.1,"5":true,"field_1":"value_2","field_2":"value_2","6":["a","b","c"],"utf-8":"éáÜŐ"}',
        ];
        $expected
            = [
            'string',
            '5',
            5,
            -5,
            1.1,
            true,
            'field_1' => 'value_2',
            'field_2' => 'value_2',
            ['a', 'b', 'c'],
            'utf-8' => 'éáÜŐ',
        ];

        // Register Mock wrapper
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', 'CRM_RcBase_Test_MockPhpStream');

        // Feed JSON to stream
        file_put_contents('php://input', $json);

        // Parse raw data from the request body
        $result = CRM_RcBase_Processor_JSON::parsePost();

        self::assertSame($expected, $result, 'Invalid JSON returned.');
    }
}
