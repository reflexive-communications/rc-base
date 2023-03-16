<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class UITest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testMenuExists()
    {
        $menu = [
            1 => [
                'attributes' => ['name' => 'menu-1'],
                'child' => [
                    ['attributes' => ['name' => 'submenu-11']],
                    ['attributes' => ['name' => 'submenu-12']],
                    [
                        'attributes' => ['name' => 'submenu-13'],
                        'child' => [
                            ['attributes' => ['name' => 'sub-submenu-131']],
                        ],
                    ],
                ],
            ],
            3 => ['attributes' => ['name' => 'menu-2']],
            4 => ['attributes' => ['name' => 'menu-3']],
        ];

        // Valid paths
        self::assertTrue(UI::menuExists($menu, 'menu-2'));
        self::assertTrue(UI::menuExists($menu, 'menu-1/submenu-12'));
        self::assertTrue(UI::menuExists($menu, 'menu-1/submenu-13/sub-submenu-131'));

        // Invalid paths
        self::assertFalse(UI::menuExists($menu, 'menu-222'));
        self::assertFalse(UI::menuExists($menu, 'menu-1/submenu-12/sub-submenu-131'));
        self::assertFalse(UI::menuExists($menu, 'menu-1/submenu-999'));
        self::assertFalse(UI::menuExists($menu, 'menu-1/submenu-13/sub-submenu-999'));
    }
}
