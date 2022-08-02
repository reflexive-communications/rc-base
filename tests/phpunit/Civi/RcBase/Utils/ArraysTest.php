<?php

namespace Civi\RcBase\Utils;

use PHPUnit\Framework\TestCase;

require_once 'Civi/RcBase/Utils/Arrays.php';

/**
 * @group unit
 */
class ArraysTest extends TestCase
{
    /**
     * @return void
     */
    public function testFilterKeyStartsWith()
    {
        $unfiltered = [
            'prefix_1' => 'value_1',
            'not_prefix_2' => 'value_2',
            'prefix_3' => 'value_3',
            'numerical index',
            55,
            true,
            5 => 'value_5',
        ];
        $expected = [
            'prefix_1' => 'value_1',
            'prefix_3' => 'value_3',
        ];
        self::assertSame($unfiltered, Arrays::filterKeyStartsWith($unfiltered, ''), 'Wrong filtered array returned for empty prefix');
        self::assertSame([], Arrays::filterKeyStartsWith($unfiltered, 'non-existent-prefix'), 'Wrong filtered array returned for non-existent prefix');
        self::assertSame($expected, Arrays::filterKeyStartsWith($unfiltered, 'prefix'), 'Wrong filtered array returned for real prefix');
    }

    /**
     * @return void
     */
    public function testFilterValueEmpty()
    {
        $unfiltered = [
            'prefix_1' => 'some string',
            [1, 2],
            'numerical index',
            55,
            true,
            'some other string',
            'not_prefix_2' => '',
            [],
            false,
            null,
        ];
        $expected = [
            'prefix_1' => 'some string',
            [1, 2],
            'numerical index',
            55,
            true,
            'some other string',
        ];
        self::assertSame($expected, Arrays::filterValueEmpty($unfiltered), 'Wrong filtered array returned');
    }
}