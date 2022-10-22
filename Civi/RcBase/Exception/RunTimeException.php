<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for general run-time errors
 */
class RunTimeException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "run-time error"
     */
    public const ERROR_CODE = 'run_time_error';

    /**
     * @param string $details (Optional) Details
     */
    public function __construct(string $details = '')
    {
        $error_msg = 'Run-time error';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
