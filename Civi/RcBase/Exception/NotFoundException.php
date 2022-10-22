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
     * @param string $details (Optional) details
     */
    public function __construct(string $details = '')
    {
        $error_msg = empty($details) ? 'Not found' : "Not found {$details}";
        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
