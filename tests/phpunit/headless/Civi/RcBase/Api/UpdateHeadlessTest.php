<?php

use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test API Update class
 *
 * @group headless
 */
class CRM_RcBase_Api_UpdateHeadlessTest extends TestCase implements HeadlessInterface
{
    /**
     * Test contact ID
     *
     * @var int
     */
    private $testContactId;

    /**
     * The setupHeadless function runs at the start of each test case, right before
     * the headless environment reboots.
     *
     * It should perform any necessary steps required for putting the database
     * in a consistent baseline -- such as loading schema and extensions.
     *
     * The utility `\Civi\Test::headless()` provides a number of helper functions
     * for managing this setup, and it includes optimizations to avoid redundant
     * setup work.
     *
     * @see \Civi\Test
     */
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Create a clean DB before running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Set up a clean DB
        \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply(true);
    }

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
            'contact_type' => 'Invalid contact type',
        ];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        Civi\RcBase\Api\Update::entity('Contact', -5, $contact);
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
                'external_identifier' => '5678',
                'job_title' => 'consul',
            ],
        ];
        $user = cv("api4 Contact.create '".json_encode($contact)."'");
        $id = $user[0]['id'];
        $data_old = cv(
            "api4 Contact.get +s contact_type,first_name,last_name,middle_name,external_identifier,job_title +w id=".$id
        );

        // Change data & update
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Crassus',
                // Add new field
                'middle_name' => 'Licinius',
                // Change value
                'external_identifier' => '56783345',
                // Delete fields
                'job_title' => null,
            ],
        ];
        Civi\RcBase\Api\Update::contact($id, $contact['values']);

        $data_new = cv(
            "api4 Contact.get +s contact_type,first_name,last_name,middle_name,external_identifier,job_title +w id=".$id
        );

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
        Civi\RcBase\Api\Update::contact($this->testContactId, $contact);
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
                'external_identifier' => '3333',
            ],
        ];
        cv("api4 Contact.create '".json_encode($contact_previous)."'");

        // Update test contact
        $contact = [
            'external_identifier' => '3333',
        ];
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        Civi\RcBase\Api\Update::contact($this->testContactId, $contact);
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
        $id = $email[0]['id'];
        $data_old = cv("api4 Email.get +s contact_id,email,location_type_id +w id=".$id);

        // Change data & update
        $email_data['values']['email'] = 'julius@senate.rome';
        $email_data['values']['location_type_id'] = 2;
        Civi\RcBase\Api\Update::email($id, $email_data['values']);

        $data_new = cv("api4 Email.get +s contact_id,email,location_type_id +w id=".$id);

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
        $id = $phone[0]['id'];
        $data_old = cv("api4 Phone.get +s contact_id,phone,location_type_id +w id=".$id);

        // Change data & update
        $phone_data['values']['phone'] = '+98765';
        Civi\RcBase\Api\Update::phone($id, $phone_data['values']);

        $data_new = cv("api4 Phone.get +s contact_id,phone,location_type_id +w id=".$id);

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
        $id = $address[0]['id'];
        $data_old = cv("api4 Address.get +s contact_id,city,location_type_id +w id=".$id);

        // Change data & update
        $address_data['values']['city'] = 'Alexandria';
        Civi\RcBase\Api\Update::address($id, $address_data['values']);

        $data_new = cv("api4 Address.get +s contact_id,city,location_type_id +w id=".$id);

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
        $contact_id_other = (int)$user_other[0]['id'];

        // Create another contact
        $contact_other_new = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Cassius',
            ],
        ];
        $user_other_new = cv("api4 Contact.create '".json_encode($contact_other_new)."'");
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
        $id = $relationship[0]['id'];
        $data_old = cv(
            "api4 Relationship.get +s contact_id_a,contact_id_b,relationship_type_id,description +w id=".$id
        );

        // Change data & update
        $relationship_data['values']['contact_id_b'] = $contact_id_other_new;

        // Update contact
        Civi\RcBase\Api\Update::relationship($id, $relationship_data['values']);

        $data_new = cv(
            "api4 Relationship.get +s contact_id_a,contact_id_b,relationship_type_id,description +w id=".$id
        );

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
        $id = $activity[0]['id'];
        $data_old = cv("api4 Activity.get +s activity_type_id,subject +w id=".$id);

        // Change data & update
        $activity_data['values']['activity_type_id'] = 2;

        // Update contact
        Civi\RcBase\Api\Update::activity($id, $activity_data['values']);

        $data_new = cv("api4 Activity.get +s activity_type_id,subject +w id=".$id);

        // Check if data changed
        $this->assertNotSame($data_old, $data_new, 'Data not changed.');

        // Check data
        unset($data_new[0]['id']);
        unset($activity_data['values']['source_contact_id']);
        unset($activity_data['values']['target_contact_id']);
        $this->assertSame($data_new[0], $activity_data['values'], 'Bad updated activity data.');
    }
}
