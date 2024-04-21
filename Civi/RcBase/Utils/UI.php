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
     * Get menu item at path
     *
     * @param array $menu Menu array
     * @param string $path Path
     *
     * @return array
     */
    public static function menuGet(array $menu, string $path): array
    {
        if (empty($menu) || empty($path)) {
            return [];
        }

        $path = explode('/', $path);
        $first = array_shift($path);

        foreach ($menu as $entry) {
            if ($entry['attributes']['name'] == $first) {
                if (empty($path)) {
                    // We arrived to the desired menu item
                    return $entry;
                } else {
                    // Recurse into remained parts
                    return self::menuGet($entry['child'] ?? [], implode('/', $path));
                }
            }
        }

        return [];
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

    /**
     * Update menu item at path
     *
     * @param array $menu Menu array
     * @param string $path Path
     * @param array $attributes New attributes
     * @param bool $recursive Set attributes recursively to all children
     *
     * @return array
     */
    public static function menuUpdate(array $menu, string $path, array $attributes, bool $recursive = false): array
    {
        if (empty($menu) || empty($path)) {
            return $menu;
        }

        $path = explode('/', $path);
        $first = array_shift($path);

        $set_attributes_recursively = function ($menu) use ($attributes, &$set_attributes_recursively) {
            foreach ($menu as $index => $item) {
                $menu[$index]['attributes'] = array_merge($item['attributes'], $attributes);
                if (isset($item['child'])) {
                    $menu[$index]['child'] = $set_attributes_recursively($item['child']);
                }
            }

            return $menu;
        };

        foreach ($menu as $index => $entry) {
            if ($entry['attributes']['name'] == $first) {
                if (empty($path)) {
                    // We arrived to the desired menu item
                    $menu[$index]['attributes'] = array_merge($entry['attributes'], $attributes);
                    if ($recursive && isset($entry['child'])) {
                        $menu[$index]['child'] = $set_attributes_recursively($entry['child']);
                    }
                } else {
                    // Recurse into remained parts
                    $menu[$index]['child'] = self::menuUpdate($entry['child'] ?? [], implode('/', $path), $attributes, $recursive);
                }
            }
        }

        return $menu;
    }
}
