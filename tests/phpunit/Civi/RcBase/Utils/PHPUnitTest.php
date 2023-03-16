<?php

namespace Civi\RcBase\Utils;

use Civi\Api4\Email;
use Civi\RcBase\HeadlessTestCase;
use CRM_Core_Session;
use CRM_RcBase_Api_Get;

/**
 * @group headless
 */
class PHPUnitTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testCounter()
    {
        $counter = PHPUnit::nextCounter();
        self::assertSame($counter + 1, PHPUnit::nextCounter(), 'Counter not incremented');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     */
    public function testCreateLoggedInUser()
    {
        $contact_id = PHPUnit::createLoggedInUser();

        $session = CRM_Core_Session::singleton();
        self::assertSame($contact_id, CRM_Core_Session::getLoggedInContactID(), 'Contact ID not set in session');
        self::assertSame('logged_in user', $session->getLoggedInContactDisplayName(), 'Wrong name for logged in user');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testCreateIndividual()
    {
        $counter = 5;
        $values = [
            'api_key' => 'some_api_key',
            'last_name' => 'Other-test',
        ];
        $contact_id = PHPUnit::createIndividual($counter, $values);

        $contact = CRM_RcBase_Api_Get::contactData($contact_id);
        self::assertSame($values['api_key'], $contact['api_key'], 'Wrong api_key returned');
        self::assertSame($values['last_name'], $contact['last_name'], 'Wrong last_name returned');
        self::assertSame("user_{$counter}", $contact['first_name'], 'Wrong first_name returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testCreateIndividualWithEmail()
    {
        $counter = 42;
        $values = [
            'signature_text' => 'Regards, testing',
            'invalid_param' => 'invalid value',
        ];
        $contact_id = PHPUnit::createIndividualWithEmail($counter, [], $values);

        self::assertSame($contact_id, CRM_RcBase_Api_Get::contactIDFromEmail("user_{$counter}@test.com"), 'Failed to create contact with email');

        $emails = Email::get()
            ->addWhere('email', '=', "user_{$counter}@test.com")
            ->setLimit(1)
            ->execute()
            ->first();
        self::assertSame($values['signature_text'], $emails['signature_text'], 'Wrong signature_text returned');
    }
}
