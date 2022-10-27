<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception for DataBase errors
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class DataBaseException extends CRM_Core_Exception
{
    /**
     * Machine-readable error message for "DB error"
     */
    public const ERROR_CODE = 'db_error';

    /**
     * @param string $details (Optional) Details
     */
    public function __construct(string $details = '')
    {
        $error_msg = 'DataBase error occurred';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
