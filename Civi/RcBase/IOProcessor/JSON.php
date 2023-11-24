<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\RunTimeException;
use Throwable;

/**
 * JSON IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 * @service IOProcessor.JSON
 */
class JSON extends Base
{
    /**
     * Parse JSON string
     *
     * @param string $json JSON to parse
     *
     * @return mixed Parsed JSON object
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function parse(string $json)
    {
        // Decode JSON
        $decoded = json_decode($json, true);

        // Check if valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('input', 'Invalid JSON received: '.json_last_error_msg());
        }

        return Base::sanitize($decoded);
    }

    /**
     * Parse JSON from stream wrappers
     * Example:
     *   - http: $stream="https://example.com/json"
     *   - file: $stream="file:///path/to/local/file"
     *   - data: $stream="data://text/plain;base64,bW9ua2V5Cg=="
     *   - php:  $stream="php://input"
     *
     * @link https://www.php.net/manual/en/wrappers.expect.php
     *
     * @param string $stream Name of JSON stream
     *
     * @return mixed Parsed data
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public static function parseStream(string $stream)
    {
        try {
            // Get contents from raw stream
            $raw = file_get_contents($stream);
        } catch (Throwable $ex) {
            throw new RunTimeException('Failed to open stream: '.$ex->getMessage(), $ex);
        }

        return self::parse($raw);
    }

    /**
     * Parse JSON from request body
     *
     * @return mixed Parsed JSON
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public static function parsePost()
    {
        return self::parseStream('php://input');
    }

    /**
     * Encode data to JSON
     *
     * @param mixed $data Object to encode
     *
     * @return false|int|string JSON
     */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
