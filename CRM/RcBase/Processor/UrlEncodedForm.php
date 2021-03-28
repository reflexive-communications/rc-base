<?php

/**
 * URL encoded IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_UrlEncodedForm extends CRM_RcBase_Processor_Base
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
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $data = $_POST;
                break;
            default:
                throw new CRM_Core_Exception('Not supported request method');
        }

        return $this->sanitize($data);
    }
}
