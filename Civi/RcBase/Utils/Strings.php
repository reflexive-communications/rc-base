<?php

namespace Civi\RcBase\Utils;

/**
 * Utilities for Strings
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Strings
{
    /**
     * Replace multiple consecutive spaces with one
     * Also replaces tabs, newlines and inside strings as well
     *
     * @param string $input Input string
     * @param bool $trim Trim string after compacting
     *
     * @return string
     */
    public static function compactWhitespace(string $input, bool $trim = true): string
    {
        $input = preg_replace('/\s+/', ' ', $input);

        return $trim ? trim($input) : $input;
    }

    /**
     * Remove comments from string
     * Removes both multi-line /* and single-line // style comments
     *
     * @param string $input Input string
     *
     * @return string Filtered string
     */
    public static function removeComments(string $input): string
    {
        if (empty($input)) {
            return '';
        }

        $input = preg_replace('@/\*.*?\*/@s', '', $input);
        $input = preg_replace('@//.*$@m', '', $input);

        return $input;
    }
}
