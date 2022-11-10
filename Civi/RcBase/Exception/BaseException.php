<?php

namespace Civi\RcBase\Exception;

use Civi;
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
     * Previous exception
     *
     * @var \Throwable|null
     */
    protected ?Throwable $previous;

    /**
     * @param string $message
     * @param string $error_code
     * @param array $error_data
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = '', string $error_code = '', array $error_data = [], ?Throwable $previous = null)
    {
        $this->errorData = $error_data + ['error_code' => $error_code];
        $this->previous = $previous;
        parent::__construct($message, $error_code, $error_data, $previous);
    }

    /**
     * @return \Throwable|null
     */
    public function getPreviousException(): ?Throwable
    {
        return $this->previous;
    }

    /**
     * Standard exception handler
     *
     * @param string $extension Extension name where exception is handled
     * @param \Civi\RcBase\Exception\BaseException $ex Exception to handle
     *
     * @return void
     */
    public static function handleException(string $extension, BaseException $ex): void
    {
        self::logException($extension, $ex);
    }

    /**
     * Write error message, previous exception (if any) and stack trace to log
     *
     * @param string $extension Extension name where exception is thrown
     * @param Throwable $ex Exception to log
     *
     * @return void
     */
    public static function logException(string $extension, Throwable $ex): void
    {
        $message = sprintf('[%s] %s', $extension, $ex->getMessage());
        $error_data = $ex instanceof CRM_Core_Exception ? $ex->getErrorData() : [];
        $previous = $ex instanceof BaseException ? $ex->getPreviousException() : null;

        if (!is_null($previous)) {
            $error_data['exception'] = $previous;
        }

        Civi::log()->error($message, $error_data);
    }
}
