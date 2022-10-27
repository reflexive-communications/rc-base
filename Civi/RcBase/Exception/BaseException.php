<?php

namespace Civi\RcBase\Exception;

use CRM_Core_Exception;
use Throwable;

/**
 * Base exception class
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class BaseException extends CRM_Core_Exception
{
    /**
     * @var array|string[]
     */
    protected array $errorData;

    /**
     * @param string $message
     * @param string $error_code
     * @param array $error_data
     * @param \Throwable|null $previous_exception
     */
    public function __construct(string $message = '', string $error_code = '', array $error_data = [], ?Throwable $previous_exception = null)
    {
        $this->errorData = $error_data + ['error_code' => $error_code];
        parent::__construct($message, $error_code, $error_data, $previous_exception);
    }
}
