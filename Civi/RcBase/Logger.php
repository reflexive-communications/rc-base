<?php

namespace Civi\RcBase;

use Civi\Core\Service\AutoServiceInterface;
use Civi\Core\Service\AutoServiceTrait;
use CRM_Core_Error;
use CRM_Core_Error_Log;

/**
 * @service log.rc-base
 */
class Logger extends CRM_Core_Error_Log implements AutoServiceInterface
{
    use AutoServiceTrait;

    /**
     * Logs with an arbitrary level to file
     *
     * @param mixed $level Log level (PSR-3)
     * @param string $message Log message
     * @param array $context Extra context data (optional)
     *  Fields:
     *  [
     *      'log_prefix' => 'my-prefix',           <-- used as log prefix, if empty, extension is used, fallback is 'debug'
     *      'extension' => 'my-extension',         <-- extension where message originated from
     *      'details' => ['foo' => 'bar']          <-- extra free form data to log
     *  ]
     */
    public function log($level, $message, array $context = []): void
    {
        if (!empty($context['extension'])) {
            $message = "{$context['extension']} | {$message}";
        }
        if (!empty($context['details'])) {
            $message .= ' | '.json_encode($context['details'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $logger = CRM_Core_Error::createDebugLogger($context['log_prefix'] ?? $context['extension'] ?? 'debug');
        $logger->log($message, $this->map[$level]);
    }
}
