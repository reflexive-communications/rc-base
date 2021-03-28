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
     * @return array|string Request parameters parsed
     *
     * @throws CRM_Core_Exception
     */
    public function input()
    {
        try {
            // Get contents from raw POST data
            $input = file_get_contents('php://input');

            // Disable external entity parsing to prevent XEE attack
            libxml_disable_entity_loader(true);

            // Load XML
            $xml = new SimpleXMLElement($input);

            // Encode & decode to JSON to convert XML_Element to array
            $data = json_encode($xml);
            $data = json_decode($data, true);

            return $this->sanitize($data);

        } catch (Exception $ex) {
            throw new CRM_Core_Exception('Unable to parse XML');
        }
    }
}
