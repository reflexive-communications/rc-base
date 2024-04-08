<?php

namespace Civi\RcBase\Utils;

use Civi;
use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\HeadlessTestCase;
use CRM_Core_Session;
use CRM_RcBase_ExtensionUtil as E;
use PHPUnit\Framework\AssertionFailedError;

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
        $uf_id = 2;
        $params = ['api_key' => 'abc123'];
        $contact_id = PHPUnit::createLoggedInUser($uf_id, $params);

        $session = CRM_Core_Session::singleton();
        self::assertSame($contact_id, CRM_Core_Session::getLoggedInContactID(), 'Contact ID not set in session');
        self::assertSame('logged_in user', $session->getLoggedInContactDisplayName(), 'Wrong name for logged in user');

        $contact = Get::entitySingle('UFMatch', [
            'select' => ['contact_id.api_key'],
            'where' => [['uf_id', '=', $uf_id]],
            'limit' => 1,
        ]);
        self::assertSame($params['api_key'], $contact['contact_id.api_key'], 'Wrong api_key returned');
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

        $contact = Get::entityByID('Contact', $contact_id);
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

        self::assertSame($contact_id, Get::contactIDByEmail("user_{$counter}@test.com"), 'Failed to create contact with email');

        $signature_text = Get::entitySingle('Email', [
            'select' => ['signature_text'],
            'where' => [['email', '=', "user_{$counter}@test.com"]],
            'limit' => 1,
        ], 'signature_text');
        self::assertSame($values['signature_text'], $signature_text, 'Wrong signature_text returned');
    }

    /**
     * @return void
     */
    public function testAssertResourcesAdded()
    {
        Civi::resources()->addScriptFile(E::LONG_NAME, 'test.js');
        Civi::resources()->addStyleFile(E::LONG_NAME, 'test.css');

        PHPUnit::assertResourcesAdded([
            E::LONG_NAME.':test.js' => 'scriptFile',
            E::LONG_NAME.':test.css' => 'styleFile',
        ]);

        // Check not added resource causes failure
        self::expectException(AssertionFailedError::class);
        PHPUnit::assertResourcesAdded([E::LONG_NAME.':not-added.css' => 'styleFile']);
    }
}
