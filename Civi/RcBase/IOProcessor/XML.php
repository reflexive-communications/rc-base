<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\RunTimeException;
use SimpleXMLElement;
use Throwable;

/**
 * XML IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class XML
{
    /**
     * Parse XML string
     *
     * @param string $xml_string XML to parse
     * @param bool $return_array Return array or SimpleXMLElement
     *
     * @return mixed Parsed XML object
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function parse(string $xml_string, bool $return_array = true)
    {
        // Disable external entity parsing to prevent XXE attack
        // In libxml versions from 2.9.0 XXE is disabled by default
        if (LIBXML_VERSION < 20900) {
            libxml_disable_entity_loader();
        }

        try {
            // Load XML
            $xml_obj = new SimpleXMLElement($xml_string);

            // If not array requested, return XML_Element
            if (!$return_array) {
                return $xml_obj;
            }

            // Encode & decode to JSON to convert XML_Element to array
            $array = json_encode($xml_obj, JSON_UNESCAPED_UNICODE);
            $array = json_decode($array, true);

            return Base::sanitize($array);
        } catch (Throwable $ex) {
            throw new InvalidArgumentException('input', 'Invalid XML received: '.$ex->getMessage(), $ex);
        }
    }

    /**
     * Read XML from stream wrappers
     * Example:
     *   - http: $stream="https://example.com/json"
     *   - file: $stream="file:///path/to/local/file"
     *   - data: $stream="data://text/plain;base64,bW9ua2V5Cg=="
     *   - php:  $stream="php://input"
     *
     * @link https://www.php.net/manual/en/wrappers.expect.php
     *
     * @param string $stream Name of XML stream
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
     * Read XML from request body
     *
     * @return mixed Parsed XML
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public static function parsePost()
    {
        return self::parseStream('php://input');
    }
}
