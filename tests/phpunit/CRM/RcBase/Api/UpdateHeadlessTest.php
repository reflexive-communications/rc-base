<?php

/**
 * Test API Update class
 *
 * @group headless
 */
class CRM_RcBase_Api_UpdateHeadlessTest extends CRM_RcBase_Api_ApiTestCase
{
    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContact()
    {
        // Create contact
        $contact = [
            'contact_type' => 'Individual',
            'first_name' => 'Marcus',
            'last_name' => 'Crassus',
            'external_identifier' => self::getNextExternalID(),
            'job_title' => 'consul',
        ];
        $contact_id = CRM_RcBase_Test_Utils::cvApi4Create('Contact', $contact);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'last_name', 'middle_name', 'external_identifier', 'job_title'],
            ["id=${contact_id}"]
        );
        $all_contact_old = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id']);

        // Change data & update
        $contact = [
            'contact_type' => 'Individual',
            'first_name' => 'Marcus',
            'last_name' => 'Crassus',
            // Add new field
            'middle_name' => 'Licinius',
            // Change value
            'external_identifier' => self::getNextExternalID(),
            // Delete fields
            'job_title' => null,
        ];
        CRM_RcBase_Api_Update::contact($contact_id, $contact);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_type', 'first_name', 'last_name', 'middle_name', 'external_identifier', 'job_title'],
            ["id=${contact_id}"]
        );
        $all_contact_new = CRM_RcBase_Test_Utils::cvApi4Get('Contact', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_contact_old), $all_contact_new, 'New contact created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $contact, 'Bad updated contact data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateInvalidEntityIdThrowsException()
    {
        // Update contact
        $contact = [
            'contact_type' => 'Individual',
        ];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Update::entity('Contact', -5, $contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContactInvalidFieldValueTypeThrowsException()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Update contact
        $contact = [
            'contact_type' => 'Invalid contact type',
        ];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: syntax error');
        CRM_RcBase_Api_Update::contact($contact_id, $contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContactUpdateToDuplicateExternalIdThrowsException()
    {
        // Create previous contact
        $contact_previous = [
            'contact_type' => 'Individual',
            'first_name' => 'Sulla',
            'external_identifier' => self::getNextExternalID(),
        ];
        $contact_id_previous = CRM_RcBase_Test_Utils::cvApi4Create('Contact', $contact_previous);

        // Create new contact
        $contact_new = [
            'contact_type' => 'Individual',
            'first_name' => 'Caesar',
            'external_identifier' => self::getNextExternalID(),
        ];
        $contact_id_new = CRM_RcBase_Test_Utils::cvApi4Create('Contact', $contact_new);

        // Update new contact
        $contact_new = ['external_identifier' => $contact_previous['external_identifier'],];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: already exists');
        CRM_RcBase_Api_Update::contact($contact_id_new, $contact_new);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateEmail()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create email
        $email = [
            'contact_id' => $contact_id,
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_id = CRM_RcBase_Test_Utils::cvApi4Create('Email', $email);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Email',
            ['contact_id', 'email', 'location_type_id'],
            ["id=${email_id}"]
        );
        $all_email_old = CRM_RcBase_Test_Utils::cvApi4Get('Email', ['id']);

        // Change data & update
        $email['email'] = 'julius@senate.rome';
        $email['location_type_id'] = 2;
        CRM_RcBase_Api_Update::email($email_id, $email);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Email',
            ['contact_id', 'email', 'location_type_id'],
            ["id=${email_id}"]
        );
        $all_email_new = CRM_RcBase_Test_Utils::cvApi4Get('Email', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_email_old), $all_email_new, 'New email created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $email, 'Bad updated email data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdatePhone()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create phone
        $phone = [
            'contact_id' => $contact_id,
            'phone' => '+1234',
            'location_type_id' => 1,
        ];
        $phone_id = CRM_RcBase_Test_Utils::cvApi4Create('Phone', $phone);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Phone',
            ['contact_id', 'phone', 'location_type_id'],
            ["id=${phone_id}"]
        );
        $all_phone_old = CRM_RcBase_Test_Utils::cvApi4Get('Phone', ['id']);

        // Change data & update
        $phone['phone'] = '+98765';
        CRM_RcBase_Api_Update::phone($phone_id, $phone);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Phone',
            ['contact_id', 'phone', 'location_type_id'],
            ["id=${phone_id}"]
        );
        $all_phone_new = CRM_RcBase_Test_Utils::cvApi4Get('Phone', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_phone_old), $all_phone_new, 'New phone created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $phone, 'Bad updated phone data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateAddress()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create address
        $address = [
            'contact_id' => $contact_id,
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $address_id = CRM_RcBase_Test_Utils::cvApi4Create('Address', $address);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Address',
            ['contact_id', 'city', 'location_type_id'],
            ["id=${address_id}"]
        );
        $all_address_old = CRM_RcBase_Test_Utils::cvApi4Get('Address', ['id']);

        // Change data & update
        $address['city'] = 'Alexandria';
        CRM_RcBase_Api_Update::address($address_id, $address);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Address',
            ['contact_id', 'city', 'location_type_id'],
            ["id=${address_id}"]
        );
        $all_address_new = CRM_RcBase_Test_Utils::cvApi4Get('Address', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_address_old), $all_address_new, 'New address created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $address, 'Bad updated address data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateRelationship()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_other = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_other_new = $this->individualCreate([], self::getNextContactSequence());

        // Create relationship
        $relationship = [
            'contact_id_a' => $contact_id,
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $relationship_id = CRM_RcBase_Test_Utils::cvApi4Create('Relationship', $relationship);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Relationship',
            ['contact_id_a', 'contact_id_b', 'relationship_type_id', 'description'],
            ["id=${relationship_id}"]
        );
        $all_relationship_old = CRM_RcBase_Test_Utils::cvApi4Get('Relationship', ['id']);

        // Change data & update
        $relationship['contact_id_b'] = $contact_id_other_new;
        CRM_RcBase_Api_Update::relationship($relationship_id, $relationship);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Relationship',
            ['contact_id_a', 'contact_id_b', 'relationship_type_id', 'description'],
            ["id=${relationship_id}"]
        );
        $all_relationship_new = CRM_RcBase_Test_Utils::cvApi4Get('Relationship', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_relationship_old), $all_relationship_new, 'New relationship created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $relationship, 'Bad updated address data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateActivity()
    {
        $contact_id = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_source = $this->individualCreate([], self::getNextContactSequence());

        // Create activity
        $activity = [
            'source_contact_id' => $contact_id_source,
            'target_contact_id' => $contact_id,
            'activity_type_id' => 1,
            'subject' => 'Test',
        ];
        $activity_id = CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Activity',
            ['activity_type_id', 'subject'],
            ["id=${activity_id}"]
        );
        $all_activity_old = CRM_RcBase_Test_Utils::cvApi4Get('Activity', ['id']);

        // Change data & update
        $activity['activity_type_id'] = 2;
        CRM_RcBase_Api_Update::activity($activity_id, $activity);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Activity',
            ['activity_type_id', 'subject'],
            ["id=${activity_id}"]
        );
        $all_activity_new = CRM_RcBase_Test_Utils::cvApi4Get('Activity', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_activity_old), $all_activity_new, 'New activity created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        unset($activity['source_contact_id']);
        unset($activity['target_contact_id']);
        self::assertSame($data_new[0], $activity, 'Bad updated address data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateGroup()
    {
        // Create group
        $group = [
            'title' => 'test group',
            'name' => 'test_group',
            'description' => 'This is some description',
            'is_active' => true,
            'is_reserved' => true,
        ];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group);

        // Old data
        $data_old = CRM_RcBase_Test_Utils::cvApi4Get(
            'Group',
            ['title', 'name', 'description', 'is_active', 'is_reserved'],
            ["id=${group_id}"]
        );
        $all_group_old = CRM_RcBase_Test_Utils::cvApi4Get('Group', ['id']);

        // Change data & update
        $group['title'] = 'Other title';
        $group['is_reserved'] = false;
        CRM_RcBase_Api_Update::group($group_id, $group);

        // New data
        $data_new = CRM_RcBase_Test_Utils::cvApi4Get(
            'Group',
            ['title', 'name', 'description', 'is_active', 'is_reserved'],
            ["id=${group_id}"]
        );
        $all_group_new = CRM_RcBase_Test_Utils::cvApi4Get('Group', ['id']);

        // Check number of entities not changed
        self::assertCount(count($all_group_old), $all_group_new, 'New group created');

        // Check if data changed
        self::assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        self::assertSame($data_new[0], $group, 'Bad updated group data.');
    }
}
