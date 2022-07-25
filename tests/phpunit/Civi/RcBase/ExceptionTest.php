<?php

namespace Civi\RcBase\Exception;

use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class ExceptionTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     */
    public function testInvalidArgumentException()
    {
        $exception = new InvalidArgumentException();
        self::assertSame('Invalid', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(InvalidArgumentException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'msg_id';
        $exception = new InvalidArgumentException($msg);
        self::assertSame("Invalid {$msg}", $exception->getMessage(), 'Wrong message returned');
    }

    /**
     * @return void
     */
    public function testMissingArgumentException()
    {
        $exception = new MissingArgumentException();
        self::assertSame('Missing', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(MissingArgumentException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'msg_id';
        $exception = new MissingArgumentException($msg);
        self::assertSame("Missing {$msg}", $exception->getMessage(), 'Wrong message returned');
    }
}
