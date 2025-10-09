<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\HeadlessTestCase;
use CRM_Utils_Check_Message;
use Psr\Log\LogLevel;

/**
 * @group headless
 */
class SystemCheckTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testChangeSeverity()
    {
        $messages = [];
        $messages[] = new CRM_Utils_Check_Message('check_error', '', '', LogLevel::ERROR);
        $messages[] = new CRM_Utils_Check_Message('check_info', '', '', LogLevel::INFO);
        $messages[] = new CRM_Utils_Check_Message('other_check_error', '', '', LogLevel::ERROR);

        SystemCheck::changeSeverity($messages, LogLevel::NOTICE, ['check_error', 'check_info']);

        foreach ($messages as $message) {
            $check = $message->getName();
            if (in_array($check, ['check_error', 'check_info'])) {
                self::assertSame(LogLevel::NOTICE, $message->getSeverity(), "Wrong severity level for {$check}");
            }
            if ($check == 'other_check_error') {
                self::assertSame(LogLevel::ERROR, $message->getSeverity(), "Wrong severity level for {$check}");
            }
        }
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testRemove()
    {
        $messages = [];
        $messages[] = new CRM_Utils_Check_Message('check_error', '', '', LogLevel::ERROR);
        $messages[] = new CRM_Utils_Check_Message('check_info', '', '', LogLevel::INFO);
        $messages[] = new CRM_Utils_Check_Message('other_check_error', '', '', LogLevel::ERROR);

        SystemCheck::remove($messages, ['check_error', 'check_info']);

        // Extract names of checks
        $checks = array_map(function ($message) {
            return $message->getName();
        }, $messages);

        self::assertFalse(in_array('check_error', $checks), 'check_error should not be present');
        self::assertFalse(in_array('check_info', $checks), 'check_info should not be present');
        self::assertTrue(in_array('other_check_error', $checks), 'other_check_error should be present');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testLowerSeverity()
    {
        $messages = [];
        $messages[] = new CRM_Utils_Check_Message('check_error', '', '', LogLevel::ERROR);
        $messages[] = new CRM_Utils_Check_Message('check_info', '', '', LogLevel::INFO);
        $messages[] = new CRM_Utils_Check_Message('other_check_error', '', '', LogLevel::ERROR);

        SystemCheck::lowerSeverity($messages, LogLevel::NOTICE, ['check_error', 'check_info']);

        $expectation = [
            'check_error' => LogLevel::NOTICE,
            'check_info' => LogLevel::INFO,
            'other_check_error' => LogLevel::ERROR,
        ];
        foreach ($messages as $message) {
            $check = $message->getName();
            self::assertSame($expectation[$check], $message->getSeverity(), "Wrong severity level for {$check}");
        }
    }
}
