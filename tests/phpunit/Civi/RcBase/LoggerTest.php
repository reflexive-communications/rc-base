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
        $logger = CRM_Core_Error::createDebugLogger('debug');
        File::truncate($logger->_filename);

        Civi::log('rc-base')->debug($message);

        // Check log
        $lines = File::readLines($logger->_filename);
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
        $logger = CRM_Core_Error::createDebugLogger($context['log_prefix']);
        File::truncate($logger->_filename);

        Civi::log('rc-base')->error($message, $context);

        // Check log
        $logger = CRM_Core_Error::createDebugLogger($context['log_prefix']);
        $lines = File::readLines($logger->_filename);
        self::assertCount(1, $lines, 'Wrong number of log messages');
        self::assertStringContainsString("{$context['extension']} | {$message}".' | '.json_encode($context['details']), $lines[0], 'Wrong log message');
    }
}
