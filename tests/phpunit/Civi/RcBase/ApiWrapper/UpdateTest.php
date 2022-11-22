<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\Contact;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\Utils\PHPUnit;
use CRM_RcBase_Api_Get;
use CRM_RcBase_HeadlessTestCase;

/**
 * Test API Update class
 *
 * @group headless
 */
class UpdateTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateContact()
    {
        // Create contact
        $params = [
            'first_name' => 'narcos',
            'last_name' => 'Crassus',
            'job_title' => 'consul',
        ];
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), $params);
        $data_old = CRM_RcBase_Api_Get::contactData($contact_id);

        // Change data & update
        $params = [
            // Change value
            'first_name' => 'Marcus',
            // Add new field
            'middle_name' => 'Licinius',
            // Keep value
            'last_name' => 'Crassus',
            // Delete fields
            'job_title' => null,
        ];
        Update::contact($contact_id, $params);
        $data_new = CRM_RcBase_Api_Get::contactData($contact_id);

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check new data
        $result = Contact::get()
            ->addSelect('first_name', 'middle_name', 'last_name', 'job_title')
            ->addWhere('id', '=', $contact_id)
            ->setLimit(1)
            ->execute();
        unset($result[0]['id']);
        self::assertSame($result[0], $params, 'Bad updated contact data.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateInvalidEntityIdThrowsException()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('ID must be positive');
        Update::entity('Contact', -5, []);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateEmptyValuesThrowsException()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('must contain at least one parameter');
        Update::entity('Contact', 5, []);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateContactInvalidFieldValueTypeThrowsException()
    {
        $contact_id = PHPUnit::createIndividual();

        self::expectException(APIException::class);
        self::expectExceptionMessage('DB Error: syntax error');
        Update::contact($contact_id, ['contact_type' => 'Invalid contact type',]);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateEmail()
    {
        $contact_id = PHPUnit::createIndividual();
        $values = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $id = Create::email($contact_id, $values);

        // Change data & update
        $values['email'] = 'julius@senate.rome';
        $values['location_type_id'] = 2;
        self::assertNotEmpty(Update::email($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdatePhone()
    {
        $contact_id = PHPUnit::createIndividual();
        $values = [
            'phone' => '+1234',
            'location_type_id' => 1,
        ];
        $id = Create::phone($contact_id, $values);

        // Change data & update
        $values['phone'] = '+98765';
        self::assertNotEmpty(Update::phone($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateAddress()
    {
        $contact_id = PHPUnit::createIndividual();
        $values = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $id = Create::address($contact_id, $values);

        // Change data & update
        $values['city'] = 'Alexandria';
        self::assertNotEmpty(Update::address($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateRelationship()
    {
        $contact_id = PHPUnit::createIndividual();
        $contact_id_other = PHPUnit::createIndividual();
        $contact_id_other_new = PHPUnit::createIndividual();
        $values = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $id = Create::relationship($contact_id, $values);

        // Change data & update
        $values['contact_id_b'] = $contact_id_other_new;
        self::assertNotEmpty(Update::relationship($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateActivity()
    {
        $contact_id = PHPUnit::createIndividual();
        $contact_id_source = PHPUnit::createIndividual();
        $values = [
            'source_contact_id' => $contact_id_source,
            'activity_type_id' => 1,
            'subject' => 'Test',
        ];
        $id = Create::activity($contact_id, $values);

        // Change data & update
        $values['activity_type_id'] = 2;
        self::assertNotEmpty(Update::activity($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateGroup()
    {
        $values = [
            'title' => 'test group',
            'name' => 'test_group',
            'description' => 'This is some description',
            'is_active' => true,
            'is_reserved' => true,
        ];
        $id = Create::group($values);

        // Change data & update
        $values['title'] = 'Other title';
        $values['is_reserved'] = false;
        self::assertNotEmpty(Update::group($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testUpdateTag()
    {
        $values = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
            'is_selectable' => false,
        ];
        $id = Create::tag($values);

        // Change data & update
        $values['is_reserved'] = false;
        $values['is_selectable'] = true;
        self::assertNotEmpty(Update::tag($id, $values), 'Empty data returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testUpdateContribution()
    {
        $contact_id = PHPUnit::createIndividual();
        $values = [
            'financial_type_id' => 1,
            'total_amount' => 15,
        ];
        $id = Create::contribution($contact_id, $values);

        // Change data & update
        unset($values['financial_type_id']);
        $values['total_amount'] = 55;
        self::assertNotEmpty(Update::contribution($id, $values), 'Empty data returned');
    }
}
