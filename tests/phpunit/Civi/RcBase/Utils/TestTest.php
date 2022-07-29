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
     * @throws \CiviCRM_API3_Exception
     */
    public function testCreateLoggedInUser()
    {
        $contact_id = Test::createLoggedInUser();

        $session = CRM_Core_Session::singleton();
        self::assertSame($contact_id, CRM_Core_Session::getLoggedInContactID(), 'Contact ID not set in session');
        self::assertSame('logged_in user', $session->getLoggedInContactDisplayName(), 'Wrong name for logged in user');
    }
}
