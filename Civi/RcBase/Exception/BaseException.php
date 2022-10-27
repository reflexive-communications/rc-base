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
}
