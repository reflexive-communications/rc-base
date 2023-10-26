<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for authorization errors
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class UnauthorizedException extends BaseException
{
    /**
     * Machine-readable error message for "unauthorized"
     */
    public const ERROR_CODE = 'unauthorized';

    /**
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = 'Unauthorized';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [], $prev_exception);
    }
}
