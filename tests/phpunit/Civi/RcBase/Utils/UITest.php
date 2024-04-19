<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class UITest extends HeadlessTestCase
{
    /**
     * Model menu
     *
     * @var array
     */
    protected static array $menu = [
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

    /**
     * @return void
     */
    public function testMenuExists()
    {
        // Valid paths
        self::assertTrue(UI::menuExists(self::$menu, 'menu-2'));
        self::assertTrue(UI::menuExists(self::$menu, 'menu-1/submenu-12'));
        self::assertTrue(UI::menuExists(self::$menu, 'menu-1/submenu-13/sub-submenu-131'));

        // Invalid paths
        self::assertFalse(UI::menuExists(self::$menu, 'menu-222'));
        self::assertFalse(UI::menuExists(self::$menu, 'menu-1/submenu-12/sub-submenu-131'));
        self::assertFalse(UI::menuExists(self::$menu, 'menu-1/submenu-999'));
        self::assertFalse(UI::menuExists(self::$menu, 'menu-1/submenu-13/sub-submenu-999'));
    }

    /**
     * @return void
     */
    public function testMenuRemove()
    {
        // Top-level menu without children
        $expected = [
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
            4 => ['attributes' => ['name' => 'menu-3']],
        ];
        self::assertSame($expected, UI::menuRemove(self::$menu, 'menu-2'), 'Wrong menu after removing existing top-level menu without children');

        // Top-level menu with children
        $expected = [
            3 => ['attributes' => ['name' => 'menu-2']],
            4 => ['attributes' => ['name' => 'menu-3']],
        ];
        self::assertSame($expected, UI::menuRemove(self::$menu, 'menu-1'), 'Wrong menu after removing existing top-level menu with children');

        // Submenu without children
        $expected = [
            1 => [
                'attributes' => ['name' => 'menu-1'],
                'child' => [
                    0 => ['attributes' => ['name' => 'submenu-11']],
                    2 => [
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
        self::assertSame($expected, UI::menuRemove(self::$menu, 'menu-1/submenu-12'), 'Wrong menu after removing existing submenu without children');

        // Submenu with children
        $expected = [
            1 => [
                'attributes' => ['name' => 'menu-1'],
                'child' => [
                    ['attributes' => ['name' => 'submenu-11']],
                    ['attributes' => ['name' => 'submenu-12']],
                ],
            ],
            3 => ['attributes' => ['name' => 'menu-2']],
            4 => ['attributes' => ['name' => 'menu-3']],
        ];
        self::assertSame($expected, UI::menuRemove(self::$menu, 'menu-1/submenu-13'), 'Wrong menu after removing existing submenu with children');

        // Sub-submenu
        $expected = [
            1 => [
                'attributes' => ['name' => 'menu-1'],
                'child' => [
                    ['attributes' => ['name' => 'submenu-11']],
                    ['attributes' => ['name' => 'submenu-12']],
                    [
                        'attributes' => ['name' => 'submenu-13'],
                        'child' => [],
                    ],
                ],
            ],
            3 => ['attributes' => ['name' => 'menu-2']],
            4 => ['attributes' => ['name' => 'menu-3']],
        ];
        self::assertSame($expected, UI::menuRemove(self::$menu, 'menu-1/submenu-13/sub-submenu-131'), 'Wrong menu after removing existing sub-submenu');

        // Remove non-existing menu
        self::assertSame(self::$menu, UI::menuRemove(self::$menu, 'menu-999'), 'Wrong menu after removing non-existing top-level menu');
        self::assertSame(self::$menu, UI::menuRemove(self::$menu, 'menu-1/submenu-999'), 'Wrong menu after removing non-existing submenu');
    }
}
