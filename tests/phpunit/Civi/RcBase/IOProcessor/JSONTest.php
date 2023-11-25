<?php

namespace Civi\RcBase\IOProcessor;

use Civi;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class JSONTest extends HeadlessTestCase
{
    /**
     * IOProcessor service
     *
     * @var \Civi\RcBase\IOProcessor\JSON
     */
    protected JSON $service;

    /**
     * @return void
     */
    public function setUpHeadless(): void
    {
        $this->service = Civi::service('IOProcessor.JSON');
    }

    /**
     * Provide valid JSON
     *
     * @return array
     */
    public function provideValidJson(): array
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
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testDecodeValidJson($json, $object)
    {
        $result = $this->service->decode($json);
        self::assertSame($object, $result, 'Invalid JSON returned.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testDecodeWithInvalidJsonThrowsException()
    {
        $json = '["string 1"=,"string 2"]';
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid JSON received');
        $this->service->decode($json);
    }

    /**
     * @return void
     */
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

        $result = JSON::encode($data);
        self::assertSame($json, $result, 'Invalid JSON returned.');
    }
}
