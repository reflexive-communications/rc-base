<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;

/**
 * Exception when something was not found
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
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
        $error_msg = 'Not found';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
