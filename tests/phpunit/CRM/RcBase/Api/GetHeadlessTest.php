<?php

use Civi\API\Exception\UnauthorizedException;

/**
 * Test API Get class
 *
 * @group headless
 */
class CRM_RcBase_Api_GetHeadlessTest extends CRM_RcBase_Test_BaseHeadlessTestCase
{
    /**
     * @throws UnauthorizedException|API_Exception
     */
    public function testGetContactIdFromEmail()
    {
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Julius',
                'last_name' => 'Caesar',
            ],
        ];
        $email = [
            'values' => [
                'email' => 'caesar@senate.rome',
            ],
        ];

        // Create user & add email
        $user = cv("api4 Contact.create '".json_encode($contact)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create"');
        $this->assertEquals(1, count($user), 'Not one result returned');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $id = (int)$user[0]['id'];
        $email['values']['contact_id'] = $id;
        $email_results = cv("api4 Email.create '".json_encode($email)."'");

        // Check results email
        $this->assertIsArray($email_results, 'Not an array returned from "cv Contact.create"');
        $this->assertEquals(1, count($email_results), 'Not one result returned');
        $this->assertIsArray($email_results[0], 'Not an array returned from "cv Contact.create"');
        $this->assertArrayHasKey('id', $email_results[0], 'ID not found.');

        // Check valid email
        $this->assertSame(
            $id,
            CRM_RcBase_Api_Get::contactIDFromEmail($email['values']['email']),
            'Bad contact ID returned'
        );

        // Check empty email
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::contactIDFromEmail("")),
            'Not null returned on empty email'
        );

        // Check non-existent email
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::contactIDFromEmail("nonexistent@rome.com")),
            'Not null returned on non-existent email'
        );
    }

    /**
     * @throws UnauthorizedException|API_Exception
     */
    public function testGetContactIdFromExternalId()
    {
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Antonius',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];

        // Create user
        $user = cv("api4 Contact.create '".json_encode($contact)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create"');
        $this->assertEquals(1, count($user), 'Not one result returned');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $id = (int)$user[0]['id'];

        // Check valid external ID
        $this->assertSame(
            $id,
            CRM_RcBase_Api_Get::contactIDFromExternalID($contact['values']['external_identifier']),
            'Bad contact ID returned'
        );

        // Check empty ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::contactIDFromExternalID("")),
            'Not null returned on empty external ID'
        );

        // Check non-existent ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::contactIDFromExternalID("11-nonexistent")),
            'Not null returned on non-existent external ID'
        );
    }

    /**
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public function testGetContactDataWithInvalidId()
    {
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::contactData(-5);
    }

    /**
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public function testGetContactDataWithValidId()
    {
        $contact_1 = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Marcus',
                'last_name' => 'Crassus',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];
        $contact_2 = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Gaius',
                'last_name' => 'Marius',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];

        // Create users
        $user_1 = cv("api4 Contact.create '".json_encode($contact_1)."'");
        $user_2 = cv("api4 Contact.create '".json_encode($contact_2)."'");

        // Check results user_1
        $this->assertIsArray($user_1, 'Not an array returned from "cv Contact.create" for "user_1"');
        $this->assertEquals(1, count($user_1), 'Not one result returned for "user_1"');
        $this->assertIsArray($user_1[0], 'Not an array returned from "cv Contact.create" for "user_1"');
        $this->assertArrayHasKey('id', $user_1[0], 'ID not found.');
        // Check results user_2
        $this->assertIsArray($user_2, 'Not an array returned from "cv Contact.create" for "user_2"');
        $this->assertEquals(1, count($user_2), 'Not one result returned for "user_2"');
        $this->assertIsArray($user_2[0], 'Not an array returned from "cv Contact.create" for "user_2"');
        $this->assertArrayHasKey('id', $user_2[0], 'ID not found.');

        // Get ID
        $id_1 = $user_1[0]['id'];
        $id_2 = $user_2[0]['id'];

        // Get data
        $data = cv("api4 Contact.get +w external_identifier=".$contact_1['values']['external_identifier']);

        // Check results data
        $this->assertIsArray($data, 'Not an array returned from "cv Contact.get" for "data"');
        $this->assertEquals(1, count($data), 'Not one result returned for "data"');
        $this->assertIsArray($data[0], 'Not an array returned from "cv Contact.get" for "data"');

        // Check if valid
        $this->assertSame(
            $data[0],
            CRM_RcBase_Api_Get::contactData($id_1),
            'Invalid contact data returned on valid contact ID.'
        );

        // Check for different
        $this->assertNotSame(
            $data[0],
            CRM_RcBase_Api_Get::contactData($id_2),
            'Invalid contact data returned for different contact ID.'
        );

        // Check non-existent contact ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::contactData(9999)),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::contactData(0);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetEmailId()
    {
        $contact_data = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Hannibal',
            ],
        ];
        $email_data = [
            'values' => [
                'location_type_id' => 1,
                'email' => 'hannibal@senate.carthago',
            ],
        ];

        // Create user
        $user = cv("api4 Contact.create '".json_encode($contact_data)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertEquals(1, count($user), 'Not one result returned for "user"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $contact_id = $user[0]['id'];

        // Add email
        $email_data['values']['contact_id'] = $contact_id;
        $email = cv("api4 Email.create '".json_encode($email_data)."'");

        // Check results email
        $this->assertIsArray($email, 'Not an array returned from "cv Email.create" for "email"');
        $this->assertEquals(1, count($email), 'Not one result returned for "email"');
        $this->assertIsArray($email[0], 'Not an array returned from "cv Email.create" for "email"');
        $this->assertArrayHasKey('id', $email[0], 'ID not found.');

        $email_id = $email[0]['id'];

        // Check valid email
        $this->assertSame(
            $email_id,
            CRM_RcBase_Api_Get::emailID($contact_id, $email_data['values']['location_type_id']),
            'Bad email ID returned'
        );

        // Check non-existent location type
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::emailID($contact_id, 5)),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::emailID(9999, $email_data['values']['location_type_id'])),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::emailID(-1, $email_data['values']['location_type_id']);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetPhoneId()
    {
        $contact_data = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Augustus',
            ],
        ];
        $phone_data = [
            'values' => [
                'location_type_id' => 1,
                'phone' => '+36101234567',
            ],
        ];

        // Create user
        $user = cv("api4 Contact.create '".json_encode($contact_data)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertEquals(1, count($user), 'Not one result returned for "user"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $contact_id = $user[0]['id'];

        // Add phone
        $phone_data['values']['contact_id'] = $contact_id;
        $phone = cv("api4 Phone.create '".json_encode($phone_data)."'");
        $phone_id = $phone[0]['id'];

        // Check results phone
        $this->assertIsArray($phone, 'Not an array returned from "cv Phone.create" for "phone"');
        $this->assertEquals(1, count($phone), 'Not one result returned for "phone"');
        $this->assertIsArray($phone[0], 'Not an array returned from "cv Phone.create" for "phone"');
        $this->assertArrayHasKey('id', $phone[0], 'ID not found.');

        // Check valid phone
        $this->assertSame(
            $phone_id,
            CRM_RcBase_Api_Get::phoneID($contact_id, $phone_data['values']['location_type_id']),
            'Bad phone ID returned'
        );

        // Check non-existent location type
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::phoneID($contact_id, 5)),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::phoneID(9999, $phone_data['values']['location_type_id'])),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::phoneID(-5, $phone_data['values']['location_type_id']);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetAddressId()
    {
        $contact_data = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Spartacus',
            ],
        ];
        $address_data = [
            'values' => [
                'location_type_id' => 1,
                'city' => 'Capua',
            ],
        ];

        // Create user
        $user = cv("api4 Contact.create '".json_encode($contact_data)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertEquals(1, count($user), 'Not one result returned for "user"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        $contact_id = $user[0]['id'];

        // Add address
        $address_data['values']['contact_id'] = $contact_id;
        $address = cv("api4 Address.create '".json_encode($address_data)."'");

        // Check results address
        $this->assertIsArray($address, 'Not an array returned from "cv Address.create" for "address"');
        $this->assertEquals(1, count($address), 'Not one result returned for "address"');
        $this->assertIsArray($address[0], 'Not an array returned from "cv Address.create" for "address"');
        $this->assertArrayHasKey('id', $address[0], 'ID not found.');

        $address_id = $address[0]['id'];

        // Check valid address
        $this->assertSame(
            $address_id,
            CRM_RcBase_Api_Get::addressID($contact_id, $address_data['values']['location_type_id']),
            'Bad address ID returned'
        );

        // Check non-existent location type
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::addressID($contact_id, 5)),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::addressID(9999, $address_data['values']['location_type_id'])),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::addressID($contact_id, 0);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetRelationshipId()
    {
        $contact = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Romulus',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];
        $contact_other = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Remus',
                'external_identifier' => self::getNextExternalID(),
            ],
        ];
        $relationship_data = [
            'values' => [
                'relationship_type_id' => 1,
            ],
        ];

        // Create users
        $user = cv("api4 Contact.create '".json_encode($contact)."'");
        $user_other = cv("api4 Contact.create '".json_encode($contact_other)."'");

        // Check results user
        $this->assertIsArray($user, 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertEquals(1, count($user), 'Not one result returned for "user"');
        $this->assertIsArray($user[0], 'Not an array returned from "cv Contact.create" for "user"');
        $this->assertArrayHasKey('id', $user[0], 'ID not found.');

        // Check results user_other
        $this->assertIsArray($user_other, 'Not an array returned from "cv Contact.create" for "user_other"');
        $this->assertEquals(1, count($user_other), 'Not one result returned for "user_other"');
        $this->assertIsArray($user_other[0], 'Not an array returned from "cv Contact.create" for "user_other"');
        $this->assertArrayHasKey('id', $user_other[0], 'ID not found.');

        $contact_id = $user[0]['id'];
        $contact_id_other = $user_other[0]['id'];

        // Add relationship
        $relationship_data['values']['contact_id_a'] = $contact_id;
        $relationship_data['values']['contact_id_b'] = $contact_id_other;
        $relationship = cv("api4 Relationship.create '".json_encode($relationship_data)."'");

        // Check results address
        $this->assertIsArray($relationship, 'Not an array returned from "cv Relationship.create" for "relationship"');
        $this->assertEquals(1, count($relationship), 'Not one result returned for "relationship"');
        $this->assertIsArray(
            $relationship[0],
            'Not an array returned from "cv Relationship.create" for "relationship"'
        );
        $this->assertArrayHasKey('id', $relationship[0], 'ID not found.');

        $relationship_id = $relationship[0]['id'];

        // Check valid relationship
        $this->assertSame(
            $relationship_id,
            CRM_RcBase_Api_Get::relationshipID(
                $contact_id,
                $contact_id_other,
                $relationship_data['values']['relationship_type_id']
            ),
            'Bad relationship ID returned'
        );

        // Check non-existent relationship type
        $this->assertTrue(
            is_null(CRM_RcBase_Api_Get::relationshipID($contact_id, $contact_id, 5)),
            'Not null returned on non-existent relationship type ID'
        );

        // Check non-existent contact ID
        $this->assertTrue(
            is_null(
                CRM_RcBase_Api_Get::relationshipID(
                    9999,
                    $contact_id_other,
                    $relationship_data['values']['relationship_type_id']
                )
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::relationshipID($contact_id, $contact_id, -5);
    }

    /**
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public function testGetDefaultLocationType()
    {
        $def_loc_type = (int)CRM_Core_BAO_LocationType::getDefault()->id;

        $this->assertSame(
            $def_loc_type,
            CRM_RcBase_Api_Get::defaultLocationTypeID(),
            'Bad default location type ID returned'
        );
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetAllActivity()
    {
        $contact_source_a = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Tiberius',
            ],
        ];
        $contact_source_b = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Claudius',
            ],
        ];
        $contact_target = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Nero',
            ],
        ];
        $contact_assignee_a = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Caligula',
            ],
        ];
        $contact_assignee_b = [
            'values' => [
                'contact_type' => 'Individual',
                'first_name' => 'Agrippa',
            ],
        ];
        $activity_data_a = [
            'values' => [
                'activity_type_id' => 1,
            ],
        ];
        $activity_data_b = [
            'values' => [
                'activity_type_id' => 2,
            ],
        ];

        // Create users
        $user_source_a = cv("api4 Contact.create '".json_encode($contact_source_a)."'");
        $user_source_b = cv("api4 Contact.create '".json_encode($contact_source_b)."'");
        $user_target = cv("api4 Contact.create '".json_encode($contact_target)."'");
        $user_assignee_a = cv("api4 Contact.create '".json_encode($contact_assignee_a)."'");
        $user_assignee_b = cv("api4 Contact.create '".json_encode($contact_assignee_b)."'");

        // Check results user_source_a
        $this->assertIsArray($user_source_a, 'Not an array returned from "cv Contact.create" for "user_source_a"');
        $this->assertEquals(1, count($user_source_a), 'Not one result returned for "user_source_a"');
        $this->assertIsArray($user_source_a[0], 'Not an array returned from "cv Contact.create" for "user_source_a"');
        $this->assertArrayHasKey('id', $user_source_a[0], 'ID not found.');

        // Check results user_source_b
        $this->assertIsArray($user_source_b, 'Not an array returned from "cv Contact.create" for "user_source_b"');
        $this->assertEquals(1, count($user_source_b), 'Not one result returned for "user_source_b"');
        $this->assertIsArray($user_source_b[0], 'Not an array returned from "cv Contact.create" for "user_source_b"');
        $this->assertArrayHasKey('id', $user_source_b[0], 'ID not found.');

        // Check results user_target
        $this->assertIsArray($user_target, 'Not an array returned from "cv Contact.create" for "user_target"');
        $this->assertEquals(1, count($user_target), 'Not one result returned for "user_target"');
        $this->assertIsArray($user_target[0], 'Not an array returned from "cv Contact.create" for "user_target"');
        $this->assertArrayHasKey('id', $user_target[0], 'ID not found.');

        // Check results user_assignee_a
        $this->assertIsArray($user_assignee_a, 'Not an array returned from "cv Contact.create" for "user_assignee_a"');
        $this->assertEquals(1, count($user_assignee_a), 'Not one result returned for "user_assignee_a"');
        $this->assertIsArray(
            $user_assignee_a[0],
            'Not an array returned from "cv Contact.create" for "user_assignee_a"'
        );
        $this->assertArrayHasKey('id', $user_assignee_a[0], 'ID not found.');

        // Check results user_assignee_b
        $this->assertIsArray($user_assignee_b, 'Not an array returned from "cv Contact.create" for "user_assignee_b"');
        $this->assertEquals(1, count($user_assignee_b), 'Not one result returned for "user_assignee_b"');
        $this->assertIsArray(
            $user_assignee_b[0],
            'Not an array returned from "cv Contact.create" for "user_assignee_b"'
        );
        $this->assertArrayHasKey('id', $user_assignee_b[0], 'ID not found.');

        $contact_id_source_a = $user_source_a[0]['id'];
        $contact_id_source_b = $user_source_b[0]['id'];
        $contact_id_target = $user_target[0]['id'];
        $contact_id_assignee_a = $user_assignee_a[0]['id'];
        $contact_id_assignee_b = $user_assignee_b[0]['id'];

        // Add activities
        $activity_data_a['values']['source_contact_id'] = $contact_id_source_a;
        $activity_data_a['values']['target_contact_id'] = $contact_id_target;
        $activity_data_a['values']['assignee_contact_id'] = $contact_id_assignee_a;
        $activity_data_b['values']['source_contact_id'] = $contact_id_source_a;
        $activity_data_b['values']['target_contact_id'] = $contact_id_target;
        $activity_data_b['values']['assignee_contact_id'] = $contact_id_assignee_a;

        cv("api4 Activity.create '".json_encode($activity_data_a)."'");
        cv("api4 Activity.create '".json_encode($activity_data_a)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");

        $activity_data_a['values']['source_contact_id'] = $contact_id_source_b;
        $activity_data_a['values']['target_contact_id'] = $contact_id_target;
        $activity_data_a['values']['assignee_contact_id'] = $contact_id_assignee_b;
        $activity_data_b['values']['source_contact_id'] = $contact_id_source_a;
        $activity_data_b['values']['target_contact_id'] = $contact_id_target;
        $activity_data_b['values']['assignee_contact_id'] = $contact_id_assignee_b;

        cv("api4 Activity.create '".json_encode($activity_data_a)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");
        cv("api4 Activity.create '".json_encode($activity_data_b)."'");

        // Check activities when contact is target
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_target,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_TARGET
        );
        $this->assertCount(9, $activities, 'Bad number of all activities when contact is the target');

        // Check activities when contact is target with filtering
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_target,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_TARGET,
            $activity_data_b['values']['activity_type_id']
        );
        $this->assertCount(6, $activities, 'Bad number of filtered activities when contact is the target');

        // Check non-existent activities returned
        $activities = CRM_RcBase_Api_Get::allActivity($contact_id_target, 5);
        $this->assertCount(0, $activities, 'Non existent activites returned');

        // Check activities when contact is source
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_source_a,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_SOURCE
        );
        $this->assertCount(8, $activities, 'Bad number of activities when contact is the source');

        // Check activities when contact is assignee
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_assignee_b,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_ASSIGNEE
        );
        $this->assertCount(4, $activities, 'Bad number of activities when contact is the assignee');

        // Check activities when contact is assignee with filtering
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_assignee_b,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_ASSIGNEE,
            $activity_data_a['values']['activity_type_id']
        );
        $this->assertCount(1, $activities, 'Bad number of activities when contact is the assignee');

        // Check invalid ID
        $this->expectException(CRM_Core_Exception::class, "Invalid exception class");
        $this->expectExceptionMessage("Invalid ID", "Invalid exception message.");
        CRM_RcBase_Api_Get::allActivity($contact_id_target, 5, -5);
    }
}
