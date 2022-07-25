<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for invalid arguments
 */
class InvalidArgumentException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "invalid argument"
     */
    public const ERROR_CODE = 'invalid_argument';

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        $error_msg = empty($message) ? 'Invalid' : "Invalid {$message}";
        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
