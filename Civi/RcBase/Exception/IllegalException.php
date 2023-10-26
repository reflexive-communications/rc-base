<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for illegal conditions, configurations or states
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class IllegalException extends BaseException
{
    /**
     * Machine-readable error message for "illegal condition"
     */
    public const ERROR_CODE = 'illegal_condition';

    /**
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = 'Illegal condition';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [], $prev_exception);
    }
}
