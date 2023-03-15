<?php

namespace Civi\RcBase\Utils;

use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class ArraysTest extends CRM_RcBase_HeadlessTestCase
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
    public function testFilterValueStartsWith()
    {
        $unfiltered = [
            'key_1' => 'prefix_1',
            'key_2' => 'not_prefix_2',
            'key_3' => 'prefix_3',
            'numerical index',
            55,
            true,
            5 => 'prefix_5',
        ];
        $expected = [
            'key_1' => 'prefix_1',
            'key_3' => 'prefix_3',
            5 => 'prefix_5',
        ];
        self::assertSame($unfiltered, Arrays::filterValueStartsWith($unfiltered, ''), 'Wrong filtered array returned for empty prefix');
        self::assertSame([], Arrays::filterValueStartsWith($unfiltered, 'non-existent-prefix'), 'Wrong filtered array returned for non-existent prefix');
        self::assertSame($expected, Arrays::filterValueStartsWith($unfiltered, 'prefix'), 'Wrong filtered array returned for real prefix');
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
        self::assertSame($expected, Arrays::filterValueNonEmpty($unfiltered), 'Wrong filtered array returned');
    }

    /**
     * @return void
     */
    public function testFilterValueSame()
    {
        $unfiltered = [
            'prefix_1' => 'some string',
            1 => [1, 2],
            'numerical index',
            2 => 55,
            3 => true,
            'some other string',
            'not_prefix_2' => '',
            4 => [],
            5 => false,
            6 => null,
        ];
        self::assertSame([3 => true], Arrays::filterValueSame($unfiltered, true), 'Wrong filtered array returned');
        self::assertSame([5 => false], Arrays::filterValueSame($unfiltered, false), 'Wrong filtered array returned');
        self::assertSame([6 => null], Arrays::filterValueSame($unfiltered, null), 'Wrong filtered array returned');
        self::assertSame(['prefix_1' => 'some string'], Arrays::filterValueSame($unfiltered, 'some string'), 'Wrong filtered array returned');
        self::assertSame([2 => 55], Arrays::filterValueSame($unfiltered, 55), 'Wrong filtered array returned');
    }

    /**
     * @return void
     */
    public function testLast()
    {
        $array = [1, 2, 3, 'last element'];
        $array_copy = array_merge($array);
        self::assertSame('last element', Arrays::last($array), 'Wrong element returned');
        self::assertSame($array_copy, $array, 'Array changed');

        self::assertNull(Arrays::last([]), 'Not null returned on empty array');
    }
}
