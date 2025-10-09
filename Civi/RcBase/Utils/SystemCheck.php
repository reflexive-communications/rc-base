<?php

namespace Civi\RcBase\Utils;

use CRM_Utils_Check;
use CRM_Utils_Check_Message;

/**
 * Manage system checks
 */
class SystemCheck
{
    /**
     * Change severity of selected checks
     *
     * @param mixed $messages System check messages
     * @param string $level New severity (\Psr\Log\LogLevel::*)
     * @param array $checks Checks to change severity
     *
     * @return void
     * @throws \CRM_Core_Exception
     * @deprecated use \Civi\RcBase\Utils\SystemCheck::lowerSeverity() instead
     */
    public static function changeSeverity($messages, string $level, array $checks): void
    {
        /* @var CRM_Utils_Check_Message[] $messages */
        foreach ($messages as $message) {
            if (in_array($message->getName(), $checks)) {
                $message->setLevel($level);
            }
        }
    }

    /**
     * Remove selected checks
     *
     * @param mixed $messages System check messages
     * @param array $checks Checks to remove
     *
     * @return void
     */
    public static function remove(&$messages, array $checks): void
    {
        /* @var CRM_Utils_Check_Message[] $messages */
        foreach ($messages as $index => $message) {
            if (in_array($message->getName(), $checks)) {
                unset($messages[$index]);
            }
        }
    }

    /**
     * Lower severity of selected checks to specified level if higher
     *
     * @param mixed $messages System check messages
     * @param string $level Severity threshold (\Psr\Log\LogLevel::*)
     * @param array $checks Checks to lower severity
     *
     * @return void
     * @throws \CRM_Core_Exception
     */
    public static function lowerSeverity($messages, string $level, array $checks): void
    {
        /* @var CRM_Utils_Check_Message[] $messages */
        foreach ($messages as $message) {
            if (in_array($message->getName(), $checks) && $message->getLevel() > CRM_Utils_Check::severityMap($level)) {
                $message->setLevel($level);
            }
        }
    }
}
