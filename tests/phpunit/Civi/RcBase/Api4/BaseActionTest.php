<?php

namespace Civi\RcBase\Api4;

require_once 'Civi/RcBase/Api4/BaseAction.php';

use Civi\Api4\Generic\Result;
use CRM_RcBase_HeadlessTestCase;

/**
 * Test stub
 */
class TestAction extends BaseAction
{
    public function _run(Result $result)
    {
    }

    public function error(string $message): array
    {
        return parent::error($message);
    }
}

/**
 * @group headless
 */
class BaseActionTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     */
    public function testError()
    {
        $message = 'There is an error :(';
        $base_action = new TestAction('entity', 'action');
        $error = $base_action->error($message);

        self::assertIsArray($error, 'Not an array returned');
        self::assertArrayHasKey('is_error', $error, 'is_error key missing');
        self::assertArrayHasKey('error_message', $error, 'error_message key missing');
        self::assertSame(true, $error['is_error'], 'Wrong is_error returned');
        self::assertSame($message, $error['error_message'], 'Wrong error_message returned');
    }
}
