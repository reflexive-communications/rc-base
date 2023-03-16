<?php

namespace Civi\RcBase\Api4;

require_once 'Civi/RcBase/Api4/ActionUtilsTrait.php';

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class ActionUtilsTraitTest extends HeadlessTestCase
{
    use ActionUtilsTrait;

    /**
     * @return void
     */
    public function testError()
    {
        $message = 'There is an error :(';
        $error = $this->error($message);

        self::assertIsArray($error, 'Not an array returned');
        self::assertCount(2, $error, 'Wrong number of fields');
        self::assertArrayHasKey('is_error', $error, 'is_error key missing');
        self::assertArrayHasKey('error_message', $error, 'error_message key missing');
        self::assertSame(true, $error['is_error'], 'Wrong is_error returned');
        self::assertSame($message, $error['error_message'], 'Wrong error_message returned');
    }
}
