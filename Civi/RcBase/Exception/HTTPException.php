<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for HTTP request-response errors
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class HTTPException extends BaseException
{
    /**
     * Machine-readable error message for "http error"
     */
    public const ERROR_CODE = 'http_error';

    /**
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = 'HTTP error';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [], $prev_exception);
    }
}
