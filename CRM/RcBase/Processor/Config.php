<?php

/**
 * Config IO Processor
 * For ini type configuration files and strings
 *
 * @deprecated use \Civi\RcBase\IOProcessor\Config instead
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_Config
{
    /**
     * Parse INI string
     *
     * @param string $ini_string INI string to parse
     * @param bool $process_sections Process sections
     * @param int $scanner_mode Scanner mode
     *
     * @return mixed Parsed config
     * @throws \CRM_Core_Exception
     */
    public static function parseIniString(
        string $ini_string,
        bool $process_sections = true,
        int $scanner_mode = INI_SCANNER_TYPED
    ) {
        try {
            $ini = parse_ini_string($ini_string, $process_sections, $scanner_mode);

            return CRM_RcBase_Processor_Base::sanitize($ini);
        } catch (Throwable $ex) {
            throw new CRM_Core_Exception('Failed to parse ini string');
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
     * @throws \CRM_Core_Exception
     */
    public static function parseIniFile(
        string $filename,
        bool $process_sections = true,
        int $scanner_mode = INI_SCANNER_TYPED
    ) {
        try {
            $ini = parse_ini_file($filename, $process_sections, $scanner_mode);

            return CRM_RcBase_Processor_Base::sanitize($ini);
        } catch (Throwable $ex) {
            throw new CRM_Core_Exception('Failed to parse ini file'.$ex->getMessage());
        }
    }
}
