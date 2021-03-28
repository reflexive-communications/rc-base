<?php

/**
 * JSON IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_JSON extends CRM_RcBase_Processor_Base
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
        // Get contents from raw POST data
        $input = file_get_contents('php://input');

        // Decode JSON
        $decoded = json_decode($input, true);

        // Check if valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CRM_Core_Exception('Not valid JSON received');
        }

        return $this->sanitize($decoded);
    }
}
