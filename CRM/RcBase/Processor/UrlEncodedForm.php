<?php

/**
 * URL encoded IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_UrlEncodedForm
{

    /**
     * Parse GET request parameters
     *
     * @return array GET parameters parsed
     */
    public static function parseGet()
    {
        return CRM_RcBase_Processor_Base::sanitize($_GET);
    }

    /**
     * Parse POST request body
     *
     * @return array Request POST parsed
     */
    public static function parsePost()
    {
        return CRM_RcBase_Processor_Base::sanitize($_POST);
    }

    /**
     * Parse request parameters
     *
     * @return array Request parameters parsed
     */
    public static function parseRequest()
    {
        return CRM_RcBase_Processor_Base::sanitize($_REQUEST);
    }

}
