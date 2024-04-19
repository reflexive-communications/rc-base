<?php

namespace Civi\RcBase\Utils;

/**
 * Utilities for UI
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class UI
{
    /**
     * Check whether menu item already exists at a path
     *
     * @param array $menu Menu array
     * @param string $path Path
     *
     * @return bool
     */
    public static function menuExists(array $menu, string $path): bool
    {
        if (empty($menu) || empty($path)) {
            return false;
        }

        $path = explode('/', $path);
        $first = array_shift($path);

        foreach ($menu as $entry) {
            // First path part found
            if ($entry['attributes']['name'] == $first) {
                // This is the last part or recurse into remained parts
                if (empty($path) || self::menuExists($entry['child'] ?? [], implode('/', $path))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove menu item at path
     *
     * @param array $menu Menu array
     * @param string $path Path
     *
     * @return array
     */
    public static function menuRemove(array $menu, string $path): array
    {
        if (empty($menu) || empty($path)) {
            return $menu;
        }

        $path = explode('/', $path);
        $first = array_shift($path);

        foreach ($menu as $index => $entry) {
            // First path part found
            if ($entry['attributes']['name'] == $first) {
                if (empty($path)) {
                    // We arrived to the desired menu item
                    unset($menu[$index]);
                } else {
                    // Recurse into remained parts
                    $menu[$index]['child'] = self::menuRemove($entry['child'] ?? [], implode('/', $path));
                }
            }
        }

        return $menu;
    }
}
