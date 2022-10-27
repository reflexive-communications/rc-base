<?php

namespace Civi\RcBase\Exception;

/**
 * Exception for corrupted data
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 *
 * @deprecated use \Civi\RcBase\Exception\DataBaseException instead
 */
class CorruptedDataException extends BaseException
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
        $error_msg = 'Corrupted data found';
        if (!empty($details)) {
            $error_msg .= ": {$details}";
        }

        parent::__construct($error_msg, self::ERROR_CODE);
    }
}
