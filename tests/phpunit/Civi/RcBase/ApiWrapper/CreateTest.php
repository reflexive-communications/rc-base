<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\Contact;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Utils\PHPUnit;

/**
 * @group headless
 */
class CreateTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateEntity()
    {
        $counter = PHPUnit::nextCounter();
        $values = [
            'contact_type' => 'Individual',
            'first_name' => 'user_'.$counter,
        ];

        // Check contact not exists beforehand
        $result = Contact::get()
            ->addSelect('id')
            ->addWhere('first_name', '=', $values['first_name'])
            ->execute();
        self::assertCount(0, $result, 'Contact should not be present');

        $contact_id = Create::entity('Contact', $values);

        // Check contact is present now and check ID also
        $result = Contact::get()
            ->addSelect('id')
            ->addWhere('first_name', '=', $values['first_name'])
            ->execute();
        self::assertCount(1, $result, 'Failed to locate contact');
        self::assertArrayHasKey('id', $result->first(), 'Id not returned');
        self::assertSame($result->first()['id'], $contact_id, 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testMissingRequiredFieldThrowsApiException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('Mandatory values missing');
        Create::entity('Activity', [
            'subject' => 'test subject',
        ]);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateContactWithExtraUnknownFields()
    {
        $counter = PHPUnit::nextCounter();
        $values = [
            'contact_type' => 'Individual',
            'first_name' => 'user_'.$counter,
            'nonexistent_field_string' => 'Ides of March',
            'nonexistent_field_int' => 15,
            'nonexistent_field_bool' => true,
        ];

        $contact_id = Create::entity('Contact', $values);

        $contacts = Contact::get()
            ->addSelect('id')
            ->addWhere('first_name', '=', $values['first_name'])
            ->execute();
        self::assertCount(1, $contacts, 'Failed to locate contact');
        self::assertArrayHasKey('id', $contacts->first(), 'Id not returned');
        self::assertSame($contacts->first()['id'], $contact_id, 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateContactWithDuplicateExternalIdThrowsException()
    {
        $counter = PHPUnit::nextCounter();
        $values = [
            'contact_type' => 'Individual',
            'external_identifier' => 'ext_'.$counter,
        ];
        Create::entity('Contact', $values);

        // Create same contact
        self::expectException(APIException::class);
        self::expectExceptionMessage('DB Error: already exists');
        Create::entity('Contact', $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateEmail()
    {
        $values = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::email($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::email(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreatePhone()
    {
        $values = [
            'phone' => '+12343243',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::phone($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::phone(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateAddress()
    {
        $values = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::address($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::address(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateRelationship()
    {
        $contact_id_other = PHPUnit::createIndividual();
        $values = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::relationship($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::relationship(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateContribution()
    {
        $values = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => '12345',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::contribution($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::contribution(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateContributionWithDuplicateTransactionIdThrowsException()
    {
        $values = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => 'duplicate',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::contribution($contact_id, $values), 'Valid ID needs to be returned');

        // Create same contribution
        self::expectException(APIException::class);
        self::expectExceptionMessage('Duplicate error');
        Create::contribution($contact_id, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateActivity()
    {
        $contact_id_source = PHPUnit::createIndividual();
        $values = [
            'source_contact_id' => $contact_id_source,
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::activity($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::activity(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testTagContact()
    {
        $values = [
            'name' => 'Test tag',
        ];
        $tag_id = Create::entity('Tag', $values);
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::tagContact($contact_id, $tag_id), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('tag ID');
        Create::tagContact($contact_id, 0);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateGroup()
    {
        $values = [
            'title' => 'Placeholder group',
            'name' => 'place_holder_group',
            'description' => 'This is some description',
        ];
        self::assertNotNull(Create::group($values), 'Valid ID needs to be returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateTag()
    {
        $values = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
            'is_selectable' => false,
        ];
        self::assertNotNull(Create::tag($values), 'Valid ID needs to be returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testCreateOptionValue()
    {
        $values = [
            'label' => 'new_status',
            'name' => 'new_status',
            'value' => 'new_state',
        ];
        self::assertNotNull(Create::optionValue('contribution_status', $values), 'Valid ID needs to be returned');

        // Check empty option group
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('option group name');
        Create::optionValue('', $values);
    }
}
