<?php

namespace Civi\RcBase\IOProcessor;

/**
 * URL encoded IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 * @service IOProcessor.UrlEncodedForm
 */
class UrlEncodedForm extends Base
{
    /**
     * Parse GET request parameters
     *
     * @return array GET parameters parsed
     */
    public static function parseGet(): array
    {
        return Base::sanitize($_GET);
    }

    /**
     * Parse POST request body
     *
     * @return array Request POST parsed
     */
    public static function parsePost(): array
    {
        return Base::sanitize($_POST);
    }

    /**
     * Parse request parameters
     *
     * @return array Request parameters parsed
     */
    public static function parseRequest(): array
    {
        return Base::sanitize($_REQUEST);
    }
}
