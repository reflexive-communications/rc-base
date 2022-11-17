<?php

namespace Civi\RcBase\Utils;

/**
 * Utilities for Arrays
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Arrays
{
    /**
     * Filter out elements where key doesn't start with specified prefix
     *
     * @param array $arr Array to filter
     * @param string $prefix Prefix
     *
     * @return array Filtered array
     */
    public static function filterKeyStartsWith(array $arr, string $prefix): array
    {
        return array_filter($arr, function ($key) use ($prefix) {
            return str_starts_with($key, $prefix);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Filter out elements where value doesn't start with specified prefix
     *
     * @param array $arr Array to filter
     * @param string $prefix Prefix
     *
     * @return array Filtered array
     */
    public static function filterValueStartsWith(array $arr, string $prefix): array
    {
        return array_filter($arr, function ($value) use ($prefix) {
            return str_starts_with($value, $prefix);
        });
    }

    /**
     * Filter out elements where value is empty
     *
     * @param array $arr Array to filter
     *
     * @return array Filtered array
     */
    public static function filterValueEmpty(array $arr): array
    {
        return array_filter($arr, function ($value) {
            return !empty($value);
        });
    }

    /**
     * Filter out elements where value is not the same (===) as the specified
     *
     * @param array $arr Array to filter
     * @param mixed $reference_value Value to compare
     *
     * @return array Filtered array
     */
    public static function filterValueSame(array $arr, $reference_value): array
    {
        return array_filter($arr, function ($array_value) use ($reference_value) {
            return $array_value === $reference_value;
        });
    }

    /**
     * Return last element of an array, leave array unchanged
     *
     * @param array $arr Array in question
     *
     * @return mixed|null
     */
    public static function last(array $arr)
    {
        return array_pop($arr);
    }
}
