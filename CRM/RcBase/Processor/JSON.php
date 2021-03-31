<?php

/**
 * JSON IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_JSON
{

    /**
     * Parse JSON string
     *
     * @param  string  $json  JSON to parse
     *
     * @return mixed Parsed JSON object
     *
     * @throws CRM_Core_Exception
     */
    public static function parse(string $json)
    {
        // Decode JSON
        $decoded = json_decode($json, true);

        // Check if valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CRM_Core_Exception('Invalid JSON received');
        }

        return CRM_RcBase_Processor_Base::sanitize($decoded);
    }

    /**
     * Parse JSON from stream wrappers
     * Example:
     *   - http: $socket="https://example.com/json"
     *   - file: $socket="file:///path/to/local/file"
     *   - data: $socket="data://text/plain;base64,bW9ua2V5Cg=="
     *   - php:  $socket="php://input"
     *
     * @link https://www.php.net/manual/en/wrappers.expect.php
     *
     * @param  string  $stream  Name of JSON stream
     *
     * @return mixed Parsed data
     *
     * @throws CRM_Core_Exception
     */
    public static function parseStream(string $stream)
    {
        // Get contents from raw stream
        $raw = file_get_contents($stream);

        return self::parse($raw);
    }

    /**
     * Parse JSON from request body
     *
     * @return mixed Parsed JSON
     *
     * @throws CRM_Core_Exception
     */
    public static function parsePost()
    {
        return self::parseStream('php://input');
    }

    /**
     * Encode data to JSON
     *
     * @param  mixed  $data  Object to encode
     *
     * @return mixed JSON
     */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

}
