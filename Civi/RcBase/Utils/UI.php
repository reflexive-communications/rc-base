<?php

namespace Civi\RcBase\Utils;

/**
 * Utilities for UI
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
        $path = explode('/', $path);
        $first = array_shift($path);

        foreach ($menu as $key => &$entry) {
            // First path part found
            if ($entry['attributes']['name'] == $first) {
                // This is the last part or recurse into remained parts
                if (empty($path) || self::menuExists($entry['child'], implode('/', $path))) {
                    return true;
                }
            }
        }
        return false;
    }
}
