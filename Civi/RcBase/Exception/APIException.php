<?php

namespace Civi\RcBase\Exception;

/**
 * Exception for API errors
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class APIException extends BaseException
{
    /**
     * Machine-readable error message for "API error"
     */
    public const ERROR_CODE = 'api_error';

    /**
     * @param string $entity Entity involved
     * @param string $action API action name that throws exception
     * @param string $reason (Optional) Reason for fail
     */
    public function __construct(string $entity, string $action, string $reason = '')
    {
        $error_msg = "Failed to execute API: {$entity}.{$action}";
        if (!empty($reason)) {
            $error_msg .= " Reason: {$reason}";
        }

        parent::__construct($error_msg, self::ERROR_CODE, [
            'entity' => $entity,
            'action' => $action,
        ]);
    }
}
