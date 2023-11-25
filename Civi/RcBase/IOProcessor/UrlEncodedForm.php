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
     * Parse URL-encoded string
     *
     * @param string $input x-www-form-urlencoded string to parse
     *
     * @return mixed Parsed URL-encoded object
     */
    public function decode(string $input)
    {
        $result = [];
        foreach (explode('&', $input) as $element) {
            $parts = explode('=', urldecode($element));
            $result[$parts[0]] = $parts[1];
        }

        return Base::sanitize($result);
    }

    /**
     * Parse POST request body
     *
     * @return array Request POST parsed
     */
    public function decodePost(): array
    {
        return Base::sanitize($_POST);
    }

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
