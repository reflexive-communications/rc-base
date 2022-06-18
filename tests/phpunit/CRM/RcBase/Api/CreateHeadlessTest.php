<?php

/**
 * Test API Create class
 *
 * @group headless
 */
class CRM_RcBase_Api_CreateHeadlessTest extends CRM_RcBase_Api_ApiTestCase
{
    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContact()
    {
        // Assemble Contact data
        $contact = $this->nextSampleIndividual();

        // Number of contacts already in DB
        $all_contact_old = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id']);

        // Create contact
        $contact_id = CRM_RcBase_Api_Create::contact($contact);

        $all_contact_new = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id']);

        self::assertCount(count($all_contact_old) + 1, $all_contact_new, 'No new contact created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id'], ["external_identifier=${contact['external_identifier']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'middle_name', 'last_name', 'external_identifier'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check ID
        self::assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check data
        unset($data[0]['id']);
        self::assertSame($data[0], $contact, 'Bad contact data returned');
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
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: already exists');
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
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id'], ["external_identifier=${contact['external_identifier']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'middle_name', 'last_name', 'external_identifier'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check ID
        self::assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check data
        unset($data[0]['id']);
        self::assertNotSame($data[0], $contact, 'Bad contact data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmail()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Number of emails already in DB
        $all_email_old = CRM_RcBase_Test_Utils::cvApi4Get('Email', ['id']);

        // Create email
        $email = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_id = CRM_RcBase_Api_Create::email($contact_id, $email);

        $all_email_new = CRM_RcBase_Test_Utils::cvApi4Get('Email', ['id']);

        self::assertCount(count($all_email_old) + 1, $all_email_new, 'No new email created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Email', ['id'], ["email=${email['email']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Email',
            ['email', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $email_id, 'Bad email ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $email, 'Bad email data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::email(0, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmailWithMissingRequiredFieldsThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create email
        $email = [
            'location_type_id' => 2,
        ];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Mandatory values missing');
        CRM_RcBase_Api_Create::email($contact_id, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmailWithNonExistentContactThrowsException()
    {
        // Get non-existent contact ID
        $contact_id = \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact');

        // Create email
        $email = [
            'email' => 'ovidius@senate.rome',
            'location_type_id' => 2,
        ];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: constraint violation');
        CRM_RcBase_Api_Create::email($contact_id, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreatePhone()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Number of phones already in DB
        $all_phone_old = CRM_RcBase_Test_Utils::cvApi4Get('Phone', ['id']);

        // Create phone
        $phone = [
            'phone' => '+12343243',
            'location_type_id' => 1,
        ];
        $phone_id = CRM_RcBase_Api_Create::phone($contact_id, $phone);

        $all_phone_new = CRM_RcBase_Test_Utils::cvApi4Get('Phone', ['id']);

        self::assertCount(count($all_phone_old) + 1, $all_phone_new, 'No new phone created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Phone', ['id'], ["phone=${phone['phone']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Phone',
            ['phone', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $phone_id, 'Bad phone ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $phone, 'Bad phone data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::phone(-4, $phone);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateAddress()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Number of addresses already in DB
        $all_address_old = CRM_RcBase_Test_Utils::cvApi4Get('Address', ['id']);

        // Create address
        $address = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $address_id = CRM_RcBase_Api_Create::address($contact_id, $address);

        $all_address_new = CRM_RcBase_Test_Utils::cvApi4Get('Address', ['id']);

        self::assertCount(count($all_address_old) + 1, $all_address_new, 'No new address created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Address', ['id'], ["city=${address['city']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Address',
            ['city', 'location_type_id'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $address_id, 'Bad address ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $address, 'Bad address data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::address(0, $address);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateRelationship()
    {
        // Create contact
        $contact_id = $this->individualCreate();
        // Create other contact
        $contact_id_other = $this->individualCreate();

        // Number of relationships already in DB
        $all_relationship_old = CRM_RcBase_Test_Utils::cvApi4Get('Relationship', ['id']);

        // Create relationship
        $relationship = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $relationship_id = CRM_RcBase_Api_Create::relationship($contact_id, $relationship);

        $all_relationship_new = CRM_RcBase_Test_Utils::cvApi4Get('Relationship', ['id']);

        self::assertCount(
            count($all_relationship_old) + 1,
            $all_relationship_new,
            'No new relationship created'
        );

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Relationship', ['id'], ["description=${relationship['description']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Relationship',
            ['contact_id_b', 'relationship_type_id', 'description'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $relationship_id, 'Bad relationship ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $relationship, 'Bad relationship data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::relationship(0, $relationship);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContribution()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Number of contributions already in DB
        $all_contribution_old = CRM_RcBase_Test_Utils::cvApi4Get('Contribution', ['id']);

        // Create contribution
        $contribution = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => '12345',
        ];
        $contribution_id = CRM_RcBase_Api_Create::contribution($contact_id, $contribution);

        $all_contribution_new = CRM_RcBase_Test_Utils::cvApi4Get('Contribution', ['id']);

        self::assertCount(
            count($all_contribution_old) + 1,
            $all_contribution_new,
            'No new contribution created'
        );

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Contribution', ['id'], ["trxn_id=${contribution['trxn_id']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contribution',
            ['financial_type_id', 'total_amount', 'trxn_id'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $contribution_id, 'Bad contribution ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $contribution, 'Bad contribution data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::contribution(-20, $contribution);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContributionWithDuplicateTransactionIdThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create contribution
        $contribution = [
            'financial_type_id' => 1,
            'total_amount' => 654.34,
            'trxn_id' => '987654',
        ];
        CRM_RcBase_Api_Create::contribution($contact_id, $contribution);

        // Create same contribution
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Duplicate error');
        CRM_RcBase_Api_Create::contribution($contact_id, $contribution);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateActivity()
    {
        // Create contact
        $contact_id = $this->individualCreate();
        // Create source contact
        $contact_id_source = $this->individualCreate();

        // Number of activities already in DB
        $all_activity_old = CRM_RcBase_Test_Utils::cvApi4Get('Activity', ['id']);

        // Create activity
        $activity = [
            'source_contact_id' => $contact_id_source,
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];
        $activity_id = CRM_RcBase_Api_Create::activity($contact_id, $activity);

        $all_activity_new = CRM_RcBase_Test_Utils::cvApi4Get('Activity', ['id']);

        self::assertCount(count($all_activity_old) + 1, $all_activity_new, 'No new activity created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Activity', ['id'], ["subject=${activity['subject']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Activity',
            ['activity_type_id', 'subject'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $activity_id, 'Bad activity ID returned');

        // Check valid data
        unset($activity['source_contact_id']);
        unset($data[0]['id']);
        self::assertSame($data[0], $activity, 'Bad activity data returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::activity(-5, $activity);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testTagContact()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create tag
        $tag = [
            'name' => 'Test tag',
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        // Add tag to contact
        $entity_tag_id = CRM_RcBase_Api_Create::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        self::assertCount(
            count($all_entity_tag_old) + 1,
            $all_entity_tag_new,
            'No new entity tag created'
        );

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id'], [
            'entity_table=civicrm_contact',
            "entity_id=${contact_id}",
            "tag_id=${tag_id}",
        ]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        // Check valid ID
        self::assertSame($id[0]['id'], $entity_tag_id, 'Bad entity tag ID returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Create::tagContact(-20, $tag_id);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testTagContactWithNonExistentTagThrowsException()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Get non-existent tag ID
        $tag_id = \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_tag');

        // Check non-existent tag ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: constraint violation');
        CRM_RcBase_Api_Create::tagContact($contact_id, $tag_id);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateGroup()
    {
        // Number of groups already in DB
        $all_groups_old = CRM_RcBase_Test_Utils::cvApi4Get('Group', ['id']);

        // Create group
        $group = [
            'title' => 'Placeholder group',
            'name' => 'place_holder_group',
            'description' => 'This is some description',
        ];
        $group_id = CRM_RcBase_Api_Create::group($group);

        $all_groups_new = CRM_RcBase_Test_Utils::cvApi4Get('Group', ['id']);

        self::assertCount(count($all_groups_old) + 1, $all_groups_new, 'No new group created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Group', ['id'], ["name=${group['name']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Group',
            ['title', 'name', 'description'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $group_id, 'Bad group ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $group, 'Bad group data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateTag()
    {
        // Number of tags already in DB
        $all_tags_old = CRM_RcBase_Test_Utils::cvApi4Get('Tag', ['id']);

        // Create tag
        $tag = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
            'is_selectable' => false,
        ];
        $tag_id = CRM_RcBase_Api_Create::tag($tag);

        $all_tags_new = CRM_RcBase_Test_Utils::cvApi4Get('Tag', ['id']);

        self::assertCount(count($all_tags_old) + 1, $all_tags_new, 'No new tag created');

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('Tag', ['id'], ["name=${tag['name']}"]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        $data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Tag',
            ['name', 'description', 'is_reserved', 'is_selectable'],
            ['id='.$id[0]['id']]
        );
        self::assertCount(1, $data, 'Not one result returned for "data"');

        // Check valid ID
        self::assertSame($id[0]['id'], $tag_id, 'Bad tag ID returned');

        // Check valid data
        unset($data[0]['id']);
        self::assertSame($data[0], $tag, 'Bad tag data returned');
    }
}
