<?php

use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test API Create class
 *
 * @group headless
 */
class CRM_RcBase_Api_CreateHeadlessTest extends TestCase implements HeadlessInterface
{
    /**
     * Test contact ID
     *
     * @var int
     */
    private $testContactId;

    /**
     * External ID counter
     *
     * @var int
     */
    private static $externalID = 0;

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

        // Check results id
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "id"');
        $this->assertEquals(1, count($user), 'Not one result returned for "id"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "id"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $this->testContactId = $user[0]['id'];
    }

    /**
     * Get next ID in sequence (auto-increment)
     *
     * @return string Next ID
     */
    private static function getNextExternalID(): string
    {
        self::$externalID++;

        return (string)self::$externalID;
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContact()
    {
        $contact = [
            'contact_type' => 'Individual',
            'first_name' => 'Scipio',
            'external_identifier' => self::getNextExternalID(),
        ];

        // Create user
        $contact_id = CRM_RcBase_Api_Create::contact($contact);

        // Get data
        $id = cv(
            "api4 Contact.get +s id +w external_identifier=".$contact['external_identifier']
        );
        $data = cv(
            "api4 Contact.get +s contact_type,first_name,external_identifier +w external_identifier=".$contact['external_identifier']
        );

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Contact.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Contact.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Contact.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Contact.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $contact, 'Bad contact data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContactWithDuplicateExternalIdThrowsException()
    {
        $contact = [
            'contact_type' => 'Individual',
            'first_name' => 'Sulla',
            'external_identifier' => self::getNextExternalID(),
        ];

        // Create contact
        CRM_RcBase_Api_Create::contact($contact);

        // Create same contact
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("DB Error: already exists", "Invalid exception message.");
        CRM_RcBase_Api_Create::contact($contact);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContactWithExtraUnknownFields()
    {
        $contact = [
            'contact_type' => 'Individual',
            'first_name' => 'Brutus',
            'external_identifier' => self::getNextExternalID(),
            'nonexistent_field_string' => 'Ides of March',
            'nonexistent_field_int' => 15,
            'nonexistent_field_bool' => true,
        ];

        // Create user
        $contact_id = CRM_RcBase_Api_Create::contact($contact);

        // Get data
        $id = cv(
            "api4 Contact.get +s id +w external_identifier=".$contact['external_identifier']
        );
        $data = cv(
            "api4 Contact.get +s contact_type,first_name,external_identifier +w external_identifier=".$contact['external_identifier']
        );

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Contact.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Contact.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Contact.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Contact.get" for "data"');

        // Check valid ID --> create was successful
        $this->assertSame($id[0]['id'], $contact_id, 'Bad contact ID returned');

        // Check data --> this should be different
        unset($data[0]['id']);
        $this->assertNotSame($data[0], $contact, 'Bad contact data returned');
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmail()
    {
        $email = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];

        // Create email
        $email_id = CRM_RcBase_Api_Create::email($this->testContactId, $email);

        // Get data
        $id = cv("api4 Email.get +s id +w email=".$email['email']);
        $data = cv("api4 Email.get +s email,location_type_id +w email=".$email['email']);

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Email.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Email.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Email.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Email.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $email_id, 'Bad email ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $email, 'Bad email data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::email(0, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateEmailWithMissingRequiredFields()
    {
        $email = [
            'location_type_id' => 2,
        ];

        // Create email
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Mandatory values missing", "Invalid exception message.");
        CRM_RcBase_Api_Create::email($this->testContactId, $email);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreatePhone()
    {
        $phone = [
            'phone' => '+12343243',
            'location_type_id' => 1,
        ];

        // Create phone
        $phone_id = CRM_RcBase_Api_Create::phone($this->testContactId, $phone);

        // Get data
        $id = cv("api4 Phone.get +s id +w phone=".$phone['phone']);
        $data = cv("api4 Phone.get +s phone,location_type_id +w phone=".$phone['phone']);

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Phone.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Phone.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Phone.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Phone.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $phone_id, 'Bad phone ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $phone, 'Bad phone data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::phone(-4, $phone);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateAddress()
    {
        $address = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];

        // Create address
        $address_id = CRM_RcBase_Api_Create::address($this->testContactId, $address);

        // Get data
        $id = cv("api4 Address.get +s id +w city=".$address['city']);
        $data = cv("api4 Address.get +s city,location_type_id +w city=".$address['city']);

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Address.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Address.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Address.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Address.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $address_id, 'Bad address ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $address, 'Bad address data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::address(0, $address);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateRelationship()
    {
        $contact_other = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Crassus',
            ],
        ];
        $user_other = cv("api4 Contact.create '".json_encode($contact_other)."'");
        $contact_id_other = (int)$user_other[0]['id'];

        $relationship = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];

        // Create relationship
        $relationship_id = CRM_RcBase_Api_Create::relationship($this->testContactId, $relationship);

        // Get data
        $id = cv("api4 Relationship.get +s id +w description=".$relationship['description']);
        $data = cv(
            "api4 Relationship.get +s contact_id_b,relationship_type_id,description +w description=".$relationship['description']
        );

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Relationship.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Relationship.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Relationship.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Relationship.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $relationship_id, 'Bad relationship ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $relationship, 'Bad relationship data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::relationship(0, $relationship);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateContribution()
    {
        $contribution = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => '12345',
        ];

        // Create contribution
        $contribution_id = CRM_RcBase_Api_Create::contribution($this->testContactId, $contribution);

        // Get data
        $id = cv("api4 Contribution.get +s id +w trxn_id=".$contribution['trxn_id']);
        $data = cv(
            "api4 Contribution.get +s financial_type_id,total_amount,trxn_id +w trxn_id=".$contribution['trxn_id']
        );

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Contribution.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Contribution.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Contribution.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Contribution.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $contribution_id, 'Bad contribution ID returned');

        // Check valid data
        unset($data[0]['id']);
        $this->assertSame($data[0], $contribution, 'Bad contribution data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::contribution(-20, $contribution);
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function testCreateActivity()
    {
        $contact_source = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Pompeius',
            ],
        ];
        $user_other = cv("api4 Contact.create '".json_encode($contact_source)."'");
        $contact_id_other = $user_other[0]['id'];

        $activity = [
            'source_contact_id' => $contact_id_other,
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];

        // Create activity
        $activity_id = CRM_RcBase_Api_Create::activity($this->testContactId, $activity);

        // Get data
        $id = cv("api4 Activity.get +s id +w subject=".$activity['subject']);
        $data = cv("api4 Activity.get +s source_contact_id,activity_type_id,subject +w subject=".$activity['subject']);

        // Check results id
        $this->assertIsArray($id, 'Not an array returned from "cv Activity.get" for "id"');
        $this->assertEquals(1, count($id), 'Not one result returned for "id"');
        $this->assertIsArray($id[0], 'Not an array returned from "cv Activity.get" for "id"');
        $this->assertArrayHasKey('id', $id[0], 'ID not found.');

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Activity.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Activity.get" for "data"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $activity_id, 'Bad activity ID returned');

        // Check valid data
        unset($activity['source_contact_id']);
        unset($data[0]['id']);
        $this->assertSame($data[0], $activity, 'Bad activity data returned');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Create::activity(-5, $activity);
    }
}
