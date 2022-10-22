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
     * @param string $argument Missing argument name
     * @param string $details (Optional) Details
     */
    public function __construct(string $argument, string $details = '')
    {
        $error_msg = "Missing {$argument}";
        if (!empty($details)) {
            $error_msg .= " Details: {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [
            'argument' => $argument,
        ]);
    }
}
