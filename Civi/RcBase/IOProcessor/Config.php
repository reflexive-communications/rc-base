<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\Exception\InvalidArgumentException;
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
            return Base::sanitize(parse_ini_string($ini_string, $process_sections, $scanner_mode));
        } catch (Throwable $ex) {
            throw new InvalidArgumentException('input', 'Failed to parse ini string: '.$ex->getMessage(), $ex);
        }
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
}
