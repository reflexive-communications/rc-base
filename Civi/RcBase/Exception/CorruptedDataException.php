<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for corrupted data
 */
class CorruptedDataException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "corrupted data"
     */
    public const ERROR_CODE = 'data_corrupted';

    /**
     * @param string $details (Optional) Details
     */
    public function __construct(string $details = '')
    {
        $error_msg = empty($details) ? 'Corrupted data' : "Corrupted data found: {$details}";
        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
