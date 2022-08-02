<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception when something was not found
 */
class NotFoundException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "not found"
     */
    public const ERROR_CODE = 'not_found';

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        $error_msg = empty($message) ? 'Not found' : "Not found {$message}";
        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
