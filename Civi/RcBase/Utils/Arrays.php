<?php

namespace Civi\RcBase\Utils;

/**
 * Utilities for Arrays
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
     * Filter out elements where value is empty
     *
     * @param array $arr Array to filter
     *
     * @return array Filtered array
     */
    public static function filterValueEmpty(array $arr): array
    {
        return array_filter($arr, function ($value, $key) {
            return !empty($value);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
