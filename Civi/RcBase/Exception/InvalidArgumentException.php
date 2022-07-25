<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for invalid arguments
 */
class InvalidArgumentException extends CRM_Core_Exception
{
    /**
     * Error code for "invalid argument"
     */
    public const ERROR_CODE_INVALID = 50;

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        $error_msg = empty($message) ? 'Invalid' : "Invalid {$message}";
        parent::__construct($error_msg, self::ERROR_CODE_INVALID);
    }
}
