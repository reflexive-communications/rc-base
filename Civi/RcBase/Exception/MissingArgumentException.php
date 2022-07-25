<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for missing arguments
 */
class MissingArgumentException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "missing argument"
     */
    public const ERROR_CODE = 'missing_argument';

    /**
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        $error_msg = empty($message) ? 'Missing' : "Missing {$message}";
        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
