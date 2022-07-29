<?php

namespace Civi\RcBase\Utils;

use CRM_Core_Session;
use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class TestTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testCreateLoggedInUser()
    {
        $contact_id = Test::createLoggedInUser();

        $session = CRM_Core_Session::singleton();
        self::assertSame($contact_id, $session->get('userID'), 'Contact ID not set in session');
    }
}
