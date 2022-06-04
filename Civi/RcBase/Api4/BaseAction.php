<?php

namespace Civi\RcBase\Api4;

use Civi\Api4\Generic\AbstractAction;

/**
 * Base Action class with helper methods for API actions
 */
abstract class BaseAction extends AbstractAction
{
    /**
     * Format error message
     *
     * @param string $message Error message
     *
     * @return array
     */
    protected function error(string $message): array
    {
        return [
            'is_error' => true,
            'error_message' => $message,
        ];
    }
}
