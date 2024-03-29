<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for general run-time errors
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class RunTimeException extends BaseException
{
    /**
     * Machine-readable error message for "run-time error"
     */
    public const ERROR_CODE = 'run_time_error';

    /**
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = 'Run-time error';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [], $prev_exception);
    }
}
