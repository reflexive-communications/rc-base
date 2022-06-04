<?php

namespace Civi\RcBase\Api4;

/**
 * Helper methods for API actions
 */
trait ActionUtilsTrait
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
