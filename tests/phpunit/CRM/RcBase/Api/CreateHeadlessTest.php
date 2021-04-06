<?php

/**
 * Test API Create class
 *
 * @group headless
 */
class CRM_RcBase_Api_CreateHeadlessTest extends CRM_RcBase_Test_BaseHeadlessTestCase
{
    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContact()
    {
        // Assemble Contact data
        $contact = $this->nextSampleIndividual();

        // Number of contacts already in DB
        $all_contact_old = $this->cvApi4Get('Contact', ['id']);

        // Create contact
        $contact_id = CRM_RcBase_Api_Create::contact($contact);

        $all_contact_new = $this->cvApi4Get('Contact', ['id']);

        $this->assertCount(count($all_contact_old) + 1, $all_contact_new, 'No new contact created');

        // Get from DB
        $id = $this->cvApi4Get('Contact', ['id'], ["external_identifier=${contact['external_identifier']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'middle_name', 'last_name', 'external_identifier'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check ID
        $this->assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check data
        unset($data[0]['id']);
        $this->assertSame($data[0], $contact, 'Bad contact data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContactWithDuplicateExternalIdThrowsException()
    {
        // Assemble Contact data
        $contact = $this->nextSampleIndividual();

        // Create contact
        CRM_RcBase_Api_Create::contact($contact);

        // Create same contact
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('DB Error: already exists', 'Invalid exception message.');
        CRM_RcBase_Api_Create::contact($contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContactWithExtraUnknownFields()
    {
        // Assemble Contact data
        $contact = $this->nextSampleIndividual();
        // Add extra unknown fields
        $contact['nonexistent_field_string'] = 'Ides of March';
        $contact['nonexistent_field_int'] = 15;
        $contact['nonexistent_field_bool'] = true;

        // Create user
        $contact_id = CRM_RcBase_Api_Create::contact($contact);

        // Get from DB
        $id = $this->cvApi4Get('Contact', ['id'], ["external_identifier=${contact['external_identifier']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'middle_name', 'last_name', 'external_identifier'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check ID
        $this->assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check data
        unset($data[0]['id']);
        $this->assertNotSame($data[0], $contact, 'Bad contact data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmail()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Number of emails already in DB
        $all_email_old = $this->cvApi4Get('Email', ['id']);

        // Create email
        $email = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_id = CRM_RcBase_Api_Create::email($contact_id, $email);

        $all_email_new = $this->cvApi4Get('Email', ['id']);

        $this->assertCount(count($all_email_old) + 1, $all_email_new, 'No new email created');

        // Get from DB
        $id = $this->cvApi4Get('Email', ['id'], ["email=${email['email']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Email',
            ['email', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $email_id, 'Bad email ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $email, 'Bad email data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::email(0, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmailWithMissingRequiredFieldsThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create email
        $email = [
            'location_type_id' => 2,
        ];
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Mandatory values missing', 'Invalid exception message.');
        CRM_RcBase_Api_Create::email($contact_id, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmailWithNonExistentContactThrowsException()
    {
        // Get non-existent contact ID
        $contact_id = $this->getNextAutoIncrementValue('civicrm_contact');

        // Create email
        $email = [
            'email' => 'ovidius@senate.rome',
            'location_type_id' => 2,
        ];
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('DB Error: constraint violation', 'Invalid exception message.');
        CRM_RcBase_Api_Create::email($contact_id, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreatePhone()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Number of phones already in DB
        $all_phone_old = $this->cvApi4Get('Phone', ['id']);

        // Create phone
        $phone = [
            'phone' => '+12343243',
            'location_type_id' => 1,
        ];
        $phone_id = CRM_RcBase_Api_Create::phone($contact_id, $phone);

        $all_phone_new = $this->cvApi4Get('Phone', ['id']);

        $this->assertCount(count($all_phone_old) + 1, $all_phone_new, 'No new phone created');

        // Get from DB
        $id = $this->cvApi4Get('Phone', ['id'], ["phone=${phone['phone']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Phone',
            ['phone', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $phone_id, 'Bad phone ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $phone, 'Bad phone data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::phone(-4, $phone);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateAddress()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Number of addresses already in DB
        $all_address_old = $this->cvApi4Get('Address', ['id']);

        // Create address
        $address = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $address_id = CRM_RcBase_Api_Create::address($contact_id, $address);

        $all_address_new = $this->cvApi4Get('Address', ['id']);

        $this->assertCount(count($all_address_old) + 1, $all_address_new, 'No new address created');

        // Get from DB
        $id = $this->cvApi4Get('Address', ['id'], ["city=${address['city']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Address',
            ['city', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $address_id, 'Bad address ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $address, 'Bad address data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::address(0, $address);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateRelationship()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());
        // Create other contact
        $contact_id_other = $this->individualCreate([], self::getNextContactSequence());

        // Number of relationships already in DB
        $all_relationship_old = $this->cvApi4Get('Relationship', ['id']);

        // Create relationship
        $relationship = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $relationship_id = CRM_RcBase_Api_Create::relationship($contact_id, $relationship);

        $all_relationship_new = $this->cvApi4Get('Relationship', ['id']);

        $this->assertCount(
            count($all_relationship_old) + 1,
            $all_relationship_new,
            'No new relationship created'
        );

        // Get from DB
        $id = $this->cvApi4Get('Relationship', ['id'], ["description=${relationship['description']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Relationship',
            ['contact_id_b', 'relationship_type_id', 'description'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $relationship_id, 'Bad relationship ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $relationship, 'Bad relationship data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::relationship(0, $relationship);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContribution()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Number of contributions already in DB
        $all_contribution_old = $this->cvApi4Get('Contribution', ['id']);

        // Create contribution
        $contribution = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => '12345',
        ];
        $contribution_id = CRM_RcBase_Api_Create::contribution($contact_id, $contribution);

        $all_contribution_new = $this->cvApi4Get('Contribution', ['id']);

        $this->assertCount(
            count($all_contribution_old) + 1,
            $all_contribution_new,
            'No new contribution created'
        );

        // Get from DB
        $id = $this->cvApi4Get('Contribution', ['id'], ["trxn_id=${contribution['trxn_id']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Contribution',
            ['financial_type_id', 'total_amount', 'trxn_id'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $contribution_id, 'Bad contribution ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $contribution, 'Bad contribution data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::contribution(-20, $contribution);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContributionWithDuplicateTransactionIdThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create contribution
        $contribution = [
            'financial_type_id' => 1,
            'total_amount' => 654.34,
            'trxn_id' => '987654',
        ];
        CRM_RcBase_Api_Create::contribution($contact_id, $contribution);

        // Create same contribution
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Duplicate error', 'Invalid exception message.');
        CRM_RcBase_Api_Create::contribution($contact_id, $contribution);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateActivity()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());
        // Create source contact
        $contact_id_source = $this->individualCreate([], self::getNextContactSequence());

        // Number of activities already in DB
        $all_activity_old = $this->cvApi4Get('Activity', ['id']);

        // Create activity
        $activity = [
            'source_contact_id' => $contact_id_source,
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];
        $activity_id = CRM_RcBase_Api_Create::activity($contact_id, $activity);

        $all_activity_new = $this->cvApi4Get('Activity', ['id']);

        $this->assertCount(count($all_activity_old) + 1, $all_activity_new, 'No new activity created');

        // Get from DB
        $id = $this->cvApi4Get('Activity', ['id'], ["subject=${activity['subject']}"]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        $data = $this->cvApi4Get(
            'Activity',
            ['activity_type_id', 'subject'],
            ['id='.$id[0]['id']]
        );
        $this->assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $activity_id, 'Bad activity ID returned');

        // Check valid data
        unset($activity['source_contact_id']);
        unset($data[0]['id']);
        $this->assertSame($data[0], $activity, 'Bad activity data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::activity(-5, $activity);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testTagContact()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create tag
        $tag = [
            'name' => 'Test tag',
        ];
        $tag_id = $this->cvApi4Create('Tag', $tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = $this->cvApi4Get('EntityTag', ['id']);

        // Add tag to contact
        $entity_tag_id = CRM_RcBase_Api_Create::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = $this->cvApi4Get('EntityTag', ['id']);

        $this->assertCount(
            count($all_entity_tag_old) + 1,
            $all_entity_tag_new,
            'No new entity tag created'
        );

        // Get from DB
        $id = $this->cvApi4Get('EntityTag', ['id'], [
            'entity_table=civicrm_contact',
            "entity_id=${contact_id}",
            "tag_id=${tag_id}",
        ]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $entity_tag_id, 'Bad entity tag ID returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('Invalid ID', 'Invalid exception message.');
        CRM_RcBase_Api_Create::tagContact(-20, $tag_id);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testTagContactWithNonExistentTagThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Get non-existent tag ID
        $tag_id = $this->getNextAutoIncrementValue('civicrm_tag');

        // Check non-existent tag ID
        $this->expectException(CRM_Core_Exception::class, 'Invalid exception class');
        $this->expectExceptionMessage('DB Error: constraint violation', 'Invalid exception message.');
        CRM_RcBase_Api_Create::tagContact($contact_id, $tag_id);
    }
}
