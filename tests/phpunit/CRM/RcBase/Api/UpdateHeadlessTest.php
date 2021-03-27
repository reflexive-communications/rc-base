<?php

/**
 * Test API Update class
 *
 * @group headless
 */
class CRM_RcBase_Api_UpdateHeadlessTest extends CRM_RcBase_Test_BaseHeadlessTestCase
{
    /**
     * Test contact ID
     *
     * @var int
     */
    private $testContactId;

    public function setUp(): void
    {
        parent::setUp();

        // Create test contact
        $contact_data = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Julius',
                'last_name' => 'Caesar',
            ],
        ];
        $user = cv("api4 Contact.create '".json_encode($contact_data)."'");
        $this->testContactId = (int)$user[0]['id'];
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateInvalidEntityIdThrowsException()
    {
        // Update test contact
        $contact = [
            'contact_type' => 'Individual',
        ];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Update::entity('Contact', -5, $contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContact()
    {
        // Create contact
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Crassus',
                'external_identifier' => self::getNextExternalID(),
                'job_title' => 'consul',
            ],
        ];
        $user = cv("api4 Contact.create '".json_encode($contact)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertEquals(1, count($user), 'Not one result returned for "user"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $id = $user[0]['id'];
        $data_old = cv(
            "api4 Contact.get +s contact_type,first_name,last_name,middle_name,external_identifier,job_title +w id=".$id
        );

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Contact.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Contact.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Crassus',
                // Add new field
                'middle_name' => 'Licinius',
                // Change value
                'external_identifier' => self::getNextExternalID(),
                // Delete fields
                'job_title' => null,
            ],
        ];
        CRM_RcBase_Api_Update::contact($id, $contact['values']);

        $data_new = cv(
            "api4 Contact.get +s contact_type,first_name,last_name,middle_name,external_identifier,job_title +w id=".$id
        );

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Contact.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Contact.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        $this->assertSame($data_new[0], $contact['values'], 'Bad updated contact data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContactInvalidFieldValueTypeThrowsException()
    {
        // Update test contact
        $contact = [
            'contact_type' => 'Invalid contact type',
        ];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("DB Error: syntax error", "Invalid exception message.");
        CRM_RcBase_Api_Update::contact($this->testContactId, $contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateContactUpdateToDuplicateExternalIdThrowsException()
    {
        // Create contact
        $contact_previous = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Sulla',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];
        cv("api4 Contact.create '".json_encode($contact_previous)."'");

        // Update test contact
        $contact = [
            'external_identifier' => $contact_previous['values']['external_identifier'],
        ];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("DB Error: already exists", "Invalid exception message.");
        CRM_RcBase_Api_Update::contact($this->testContactId, $contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateEmail()
    {
        // Create email
        $email_data = [
            'values' => [
                'contact_id' => $this->testContactId,
                'email' => 'ceasar@senate.rome',
                'location_type_id' => 1,
            ],
        ];
        $email = cv("api4 Email.create '".json_encode($email_data)."'");

        // Check results email
        $this->assertIsArray($email, 'Not an array returned from "cv Email.create" for "email"');
        $this->assertEquals(1, count($email), 'Not one result returned for "email"');
        $this->assertIsArray($email[0], 'Not an array returned from "cv Email.create" for "email"');
        $this->assertArrayHasKey('id', $email[0], 'ID not found.');

        $id = $email[0]['id'];

        $data_old = cv("api4 Email.get +s contact_id,email,location_type_id +w id=".$id);

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Email.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Email.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $email_data['values']['email'] = 'julius@senate.rome';
        $email_data['values']['location_type_id'] = 2;
        CRM_RcBase_Api_Update::email($id, $email_data['values']);

        $data_new = cv("api4 Email.get +s contact_id,email,location_type_id +w id=".$id);

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Email.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Email.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        $this->assertSame($data_new[0], $email_data['values'], 'Bad updated email data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdatePhone()
    {
        // Create phone
        $phone_data = [
            'values' => [
                'contact_id' => $this->testContactId,
                'phone' => '+1234',
                'location_type_id' => 1,
            ],
        ];
        $phone = cv("api4 Phone.create '".json_encode($phone_data)."'");

        // Check results phone
        $this->assertIsArray($phone, 'Not an array returned from "cv Phone.create" for "phone"');
        $this->assertEquals(1, count($phone), 'Not one result returned for "phone"');
        $this->assertIsArray($phone[0], 'Not an array returned from "cv Phone.create" for "phone"');
        $this->assertArrayHasKey('id', $phone[0], 'ID not found.');

        $id = $phone[0]['id'];

        $data_old = cv("api4 Phone.get +s contact_id,phone,location_type_id +w id=".$id);

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Phone.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Phone.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $phone_data['values']['phone'] = '+98765';
        CRM_RcBase_Api_Update::phone($id, $phone_data['values']);

        $data_new = cv("api4 Phone.get +s contact_id,phone,location_type_id +w id=".$id);

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Phone.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Phone.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        $this->assertSame($data_new[0], $phone_data['values'], 'Bad updated phone data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateAddress()
    {
        // Create address
        $address_data = [
            'values' => [
                'contact_id' => $this->testContactId,
                'city' => 'Rome',
                'location_type_id' => 1,
            ],
        ];
        $address = cv("api4 Address.create '".json_encode($address_data)."'");

        // Check results address
        $this->assertIsArray($address, 'Not an array returned from "cv Address.create" for "address"');
        $this->assertEquals(1, count($address), 'Not one result returned for "address"');
        $this->assertIsArray($address[0], 'Not an array returned from "cv Address.create" for "address"');
        $this->assertArrayHasKey('id', $address[0], 'ID not found.');

        $id = $address[0]['id'];

        $data_old = cv("api4 Address.get +s contact_id,city,location_type_id +w id=".$id);

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Address.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Address.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $address_data['values']['city'] = 'Alexandria';
        CRM_RcBase_Api_Update::address($id, $address_data['values']);

        $data_new = cv("api4 Address.get +s contact_id,city,location_type_id +w id=".$id);

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Address.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Address.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        $this->assertSame($data_new[0], $address_data['values'], 'Bad updated address data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateRelationship()
    {
        // Create other contact
        $contact_other = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Ovidius',
            ],
        ];
        $user_other = cv("api4 Contact.create '".json_encode($contact_other)."'");

        // Check results user_other
        $this->assertIsArray($user_other, 'Not an array returned from "cv Contact.create" for "user_other"');
        $this->assertEquals(1, count($user_other), 'Not one result returned for "user_other"');
        $this->assertIsArray($user_other[0], 'Not an array returned from "cv Contact.create" for "user_other"');
        $this->assertArrayHasKey('id', $user_other[0], 'ID not found.');

        $contact_id_other = (int)$user_other[0]['id'];

        // Create another contact
        $contact_other_new = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Cassius',
            ],
        ];
        $user_other_new = cv("api4 Contact.create '".json_encode($contact_other_new)."'");

        // Check results user_other_new
        $this->assertIsArray($user_other_new, 'Not an array returned from "cv Contact.create" for "user_other_new"');
        $this->assertEquals(1, count($user_other_new), 'Not one result returned for "user_other_new"');
        $this->assertIsArray($user_other_new[0], 'Not an array returned from "cv Contact.create" for "user_other_new"');
        $this->assertArrayHasKey('id', $user_other_new[0], 'ID not found.');

        $contact_id_other_new = (int)$user_other_new[0]['id'];

        // Create relationship
        $relationship_data = [
            'values' => [
                'contact_id_a' => $this->testContactId,
                'contact_id_b' => $contact_id_other,
                'relationship_type_id' => 1,
                'description' => 'Test',
            ],
        ];
        $relationship = cv("api4 Relationship.create '".json_encode($relationship_data)."'");

        // Check results relationship
        $this->assertIsArray($relationship, 'Not an array returned from "cv Relationship.create" for "relationship"');
        $this->assertEquals(1, count($relationship), 'Not one result returned for "relationship"');
        $this->assertIsArray(
            $relationship[0],
            'Not an array returned from "cv Relationship.create" for "relationship"'
        );
        $this->assertArrayHasKey('id', $relationship[0], 'ID not found.');

        $id = $relationship[0]['id'];

        $data_old = cv(
            "api4 Relationship.get +s contact_id_a,contact_id_b,relationship_type_id,description +w id=".$id
        );

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Relationship.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Relationship.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $relationship_data['values']['contact_id_b'] = $contact_id_other_new;

        // Update contact
        CRM_RcBase_Api_Update::relationship($id, $relationship_data['values']);

        $data_new = cv(
            "api4 Relationship.get +s contact_id_a,contact_id_b,relationship_type_id,description +w id=".$id
        );

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Relationship.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Relationship.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        $this->assertSame($data_new[0], $relationship_data['values'], 'Bad updated relationship data.');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testUpdateActivity()
    {
        // Create source contact
        $contact_source = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Brutus',
            ],
        ];
        $user_source = cv("api4 Contact.create '".json_encode($contact_source)."'");

        // Check results user_other_new
        $this->assertIsArray($user_source, 'Not an array returned from "cv Contact.create" for "user_source"');
        $this->assertEquals(1, count($user_source), 'Not one result returned for "user_source"');
        $this->assertIsArray($user_source[0], 'Not an array returned from "cv Contact.create" for "user_source"');
        $this->assertArrayHasKey('id', $user_source[0], 'ID not found.');

        $contact_id_source = $user_source[0]['id'];

        // Create activity
        $activity_data = [
            'values' => [
                'source_contact_id' => $contact_id_source,
                'target_contact_id' => $this->testContactId,
                'activity_type_id' => 1,
                'subject' => 'Test',
            ],
        ];
        $activity = cv("api4 Activity.create '".json_encode($activity_data)."'");

        // Check results activity
        $this->assertIsArray($activity, 'Not an array returned from "cv Activity.create" for "activity"');
        $this->assertEquals(1, count($activity), 'Not one result returned for "activity"');
        $this->assertIsArray($activity[0], 'Not an array returned from "cv Activity.create" for "activity"');
        $this->assertArrayHasKey('id', $activity[0], 'ID not found.');

        $id = $activity[0]['id'];

        $data_old = cv("api4 Activity.get +s activity_type_id,subject +w id=".$id);

        // Check results data_old
        $this->assertIsArray($data_old, 'Not an array returned from "cv Activity.get" for "data_old"');
        $this->assertEquals(1, count($data_old), 'Not one result returned for "data_old"');
        $this->assertIsArray($data_old[0], 'Not an array returned from "cv Activity.get" for "data_old"');
        $this->assertArrayHasKey('id', $data_old[0], 'ID not found.');

        // Change data & update
        $activity_data['values']['activity_type_id'] = 2;

        // Update contact
        CRM_RcBase_Api_Update::activity($id, $activity_data['values']);

        $data_new = cv("api4 Activity.get +s activity_type_id,subject +w id=".$id);

        // Check results data_new
        $this->assertIsArray($data_new, 'Not an array returned from "cv Activity.get" for "data_new"');
        $this->assertEquals(1, count($data_new), 'Not one result returned for "data_new"');
        $this->assertIsArray($data_new[0], 'Not an array returned from "cv Activity.get" for "data_new"');
        $this->assertArrayHasKey('id', $data_new[0], 'ID not found.');

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        unset($activity_data['values']['source_contact_id']);
        unset($activity_data['values']['target_contact_id']);
        $this->assertSame($data_new[0], $activity_data['values'], 'Bad updated activity data.');
    }
}
