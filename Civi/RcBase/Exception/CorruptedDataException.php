<?php

namespace Civi\RcBase\Exception;

use Throwable;

/**
 * Exception for corrupted data
 *
 * @deprecated use \Civi\RcBase\Exception\DataBaseException instead
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CorruptedDataException extends BaseException
{
    /**
     * Machine-readable error message for "corrupted data"
     */
    public const ERROR_CODE = 'data_corrupted';

    /**
     * @param string $details (Optional) Details
     * @param \Throwable|null $prev_exception
     */
    public function __construct(string $details = '', ?Throwable $prev_exception = null)
    {
        $error_msg = 'Corrupted data found';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [], $prev_exception);
    }
}
