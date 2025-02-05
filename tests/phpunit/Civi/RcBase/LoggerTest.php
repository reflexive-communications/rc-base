<?php

namespace Civi\RcBase;

use Civi;
use Civi\RcBase\Utils\File;
use CRM_Core_Error;
use CRM_RcBase_ExtensionUtil as E;

/**
 * @group headless
 */
class LoggerTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testLogMessageWithoutContext()
    {
        $message = 'test message';

        // Clear log as it may contain messages from previous tests
        $filename = CRM_Core_Error::generateLogFileName('debug');
        File::truncate($filename);

        Civi::log('rc-base')->debug($message);

        // Check log
        $lines = File::readLines($filename);
        self::assertCount(1, $lines, 'Wrong number of log messages');
        self::assertStringContainsString($message, $lines[0], 'Wrong log message');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testLogMessageWithContext()
    {
        $message = 'some error message';
        $context = [
            'log_prefix' => 'logger-test',
            'extension' => E::LONG_NAME,
            'details' => ['foo' => 'bar'],
        ];

        // Clear log as it may contain messages from previous tests
        $filename = CRM_Core_Error::generateLogFileName($context['log_prefix']);
        File::truncate($filename);

        Civi::log('rc-base')->error($message, $context);

        // Check log
        $lines = File::readLines($filename);
        self::assertCount(1, $lines, 'Wrong number of log messages');
        self::assertStringContainsString("{$context['extension']} | {$message}".' | '.json_encode($context['details']), $lines[0], 'Wrong log message');
    }
}
