<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\Exception\InvalidArgumentException;
use Exception;
use Throwable;

/**
 * Config IO Processor
 * For ini type configuration files and strings
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Config
{
    /**
     * Parse INI string
     *
     * @param string $ini_string INI string to parse
     * @param bool $process_sections Process sections
     * @param int $scanner_mode Scanner mode
     *
     * @return mixed Parsed config
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function parseIniString(string $ini_string, bool $process_sections = true, int $scanner_mode = INI_SCANNER_TYPED)
    {
        try {
            // Temporarily switch-off error handler to be able to catch all errors
            set_error_handler(function (int $errno, string $message) {
                throw new Exception($message);
            });
            $result = parse_ini_string($ini_string, $process_sections, $scanner_mode);
        } catch (Throwable $ex) {
            // Exception is caught --> switch back to old one
            restore_error_handler();
            throw new InvalidArgumentException('input', 'Failed to parse ini string: '.$ex->getMessage(), $ex);
        }
        // No exception thrown --> switch back to old one
        restore_error_handler();

        return Base::sanitize($result);
    }

    /**
     * Parse INI file
     *
     * @param string $filename INI file to parse
     * @param bool $process_sections Process sections
     * @param int $scanner_mode Scanner mode
     *
     * @return mixed Parsed config
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function parseIniFile(string $filename, bool $process_sections = true, int $scanner_mode = INI_SCANNER_TYPED)
    {
        try {
            return Base::sanitize(parse_ini_file($filename, $process_sections, $scanner_mode));
        } catch (Throwable $ex) {
            throw new InvalidArgumentException('input', 'Failed to parse ini file: '.$ex->getMessage(), $ex);
        }
    }

    /**
     * Create INI string from "dictionary" (associative array of name-value pairs aka hash)
     *
     * @param array $dictionary
     *
     * @return string
     */
    public static function iniStringFromDictionary(array $dictionary): string
    {
        $result = [];
        foreach ($dictionary as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $value = 'null';
            }
            $result[] = "${key}=${value}";
        }

        return implode("\n", $result);
    }
}
