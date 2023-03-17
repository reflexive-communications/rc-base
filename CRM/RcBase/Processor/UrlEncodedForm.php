<?php

/**
 * URL encoded IO Processor
 *
 * @deprecated use \Civi\RcBase\IOProcessor\UrlEncodedForm instead
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
    public static function parseGet(): array
    {
        return CRM_RcBase_Processor_Base::sanitize($_GET);
    }

    /**
     * Parse POST request body
     *
     * @return array Request POST parsed
     */
    public static function parsePost(): array
    {
        return CRM_RcBase_Processor_Base::sanitize($_POST);
    }

    /**
     * Parse request parameters
     *
     * @return array Request parameters parsed
     */
    public static function parseRequest(): array
    {
        return CRM_RcBase_Processor_Base::sanitize($_REQUEST);
    }
}
