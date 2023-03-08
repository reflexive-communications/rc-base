<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for invalid arguments
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class InvalidArgumentException extends BaseException
{
    /**
     * Machine-readable error message for "invalid argument"
     */
    public const ERROR_CODE = 'invalid_argument';

    /**
     * @param string $argument Invalid argument name
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $argument, string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = "Invalid {$argument}";
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, ['argument' => $argument], $prev_exception);
    }
}
