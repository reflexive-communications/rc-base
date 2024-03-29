<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for missing arguments
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class MissingArgumentException extends BaseException
{
    /**
     * Machine-readable error message for "missing argument"
     */
    public const ERROR_CODE = 'missing_argument';

    /**
     * @param string $argument Missing argument name
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $argument, string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = "Missing {$argument}";
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, ['argument' => $argument], $prev_exception);
    }
}
