<?php

/**
 * XML IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_XML extends CRM_RcBase_Processor_Base
{
    /**
     * Process input
     *
     * @param string $xml_string
     * @return mixed Parsed XML object
     *
     * @throws CRM_Core_Exception
     */
    public static function input(string $xml_string)
    {
        // Disable external entity parsing to prevent XEE attack
        libxml_disable_entity_loader(true);

        try {
            // Load XML
            $xml = new SimpleXMLElement($xml_string);

            // Encode & decode to JSON to convert XML_Element to array
            $data = json_encode($xml, JSON_UNESCAPED_UNICODE);
            $data = json_decode($data, true);
        } catch (Throwable $ex) {
            throw new CRM_Core_Exception('Unable to parse XML');
        }

        return CRM_RcBase_Processor_Base::sanitize($data);
    }

    /**
     * Read XML from stream wrappers
     * Example:
     *   - http: $socket="https://example.com/json"
     *   - file: $socket="file:///path/to/local/file"
     *   - data: $socket="data://text/plain;base64,bW9ua2V5Cg=="
     *   - php:  $socket="php://input"
     *
     * @link https://www.php.net/manual/en/wrappers.expect.php
     *
     * @param string $stream Name of XML stream
     *
     * @return mixed Parsed data
     *
     * @throws CRM_Core_Exception
     */
    public static function inputStream(string $stream)
    {
        // Get contents from raw stream
        $raw = file_get_contents($stream);

        return self::input($raw);
    }

    /**
     * Read XML from request body
     *
     * @return mixed Parsed XML
     *
     * @throws CRM_Core_Exception
     */
    public static function inputPost()
    {
        return self::inputStream('php://input');
    }
}
