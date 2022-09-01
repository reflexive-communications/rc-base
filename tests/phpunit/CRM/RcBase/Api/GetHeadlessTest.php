<?php

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\GroupContact;
use Civi\RcBase\Utils\PHPUnit;

/**
 * Test API Get class
 *
 * @group headless
 */
class CRM_RcBase_Api_GetHeadlessTest extends CRM_RcBase_Api_ApiTestCase
{
    /**
     * @throws UnauthorizedException
     * @throws API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetContactIdFromEmail()
    {
        // Create contacts
        $contact_id_a = $this->individualCreate();
        $contact_id_b = $this->individualCreate();
        $contact_id_c = $this->individualCreate(['is_deleted' => true,]);

        // Create emails
        $email_a = [
            'contact_id' => $contact_id_a,
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_a);
        $email_b = [
            'contact_id' => $contact_id_a,
            'email' => 'ceasar@home.rome',
            'location_type_id' => 2,
        ];
        CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_b);
        $email_c = [
            'contact_id' => $contact_id_b,
            'email' => 'antonius@senate.rome',
            'location_type_id' => 1,
        ];
        CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_c);
        $email_d = [
            'contact_id' => $contact_id_c,
            'email' => 'tiberius@senate.rome',
            'location_type_id' => 1,
        ];
        CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_d);

        // Check valid email
        self::assertSame(
            $contact_id_a,
            CRM_RcBase_Api_Get::contactIDFromEmail($email_a['email']),
            'Bad contact ID returned'
        );
        self::assertSame(
            $contact_id_a,
            CRM_RcBase_Api_Get::contactIDFromEmail($email_b['email']),
            'Bad contact ID returned'
        );
        self::assertSame(
            $contact_id_b,
            CRM_RcBase_Api_Get::contactIDFromEmail($email_c['email']),
            'Bad contact ID returned'
        );

        // Check empty email
        self::assertNull(CRM_RcBase_Api_Get::contactIDFromEmail(''), 'Not null returned on empty email');

        // Check deleted contact
        self::assertNull(CRM_RcBase_Api_Get::contactIDFromEmail($email_d['email']), 'Not null returned on deleted contact');

        // Check non-existent email
        self::assertNull(
            CRM_RcBase_Api_Get::contactIDFromEmail('nonexistent@rome.com'),
            'Not null returned on non-existent email'
        );
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetContactIdFromExternalId()
    {
        // Create contacts
        $external_id_a = self::getNextExternalID();
        $external_id_b = self::getNextExternalID();
        $external_id_c = self::getNextExternalID();

        $contact_id_a = $this->individualCreate(['external_identifier' => $external_id_a]);
        $contact_id_b = $this->individualCreate(['external_identifier' => $external_id_b]);
        $contact_id_c = $this->individualCreate(['external_identifier' => $external_id_c, 'is_deleted' => true,]);

        // Check valid id
        self::assertSame(
            $contact_id_a,
            CRM_RcBase_Api_Get::contactIDFromExternalID($external_id_a),
            'Bad contact ID returned'
        );
        self::assertSame(
            $contact_id_b,
            CRM_RcBase_Api_Get::contactIDFromExternalID($external_id_b),
            'Bad contact ID returned'
        );

        // Check empty id
        self::assertNull(CRM_RcBase_Api_Get::contactIDFromExternalID(''), 'Not null returned on empty email');

        // Check deleted contact
        self::assertNull(CRM_RcBase_Api_Get::contactIDFromExternalID($external_id_c), 'Not null returned on deleted contact');

        // Check non-existent id
        self::assertNull(
            CRM_RcBase_Api_Get::contactIDFromExternalID('11-nonexistent'),
            'Not null returned on non-existent email'
        );
    }

    /**
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public function testGetContactData()
    {
        // Create contacts
        $external_id_a = self::getNextExternalID();
        $external_id_b = self::getNextExternalID();

        $contact_id_a = $this->individualCreate(['external_identifier' => $external_id_a]);
        $contact_id_b = $this->individualCreate(['external_identifier' => $external_id_b]);

        // Get data
        $data_a = CRM_RcBase_Test_Utils::cvApi4Get('Contact', [], ["external_identifier=${external_id_a}"]);
        $data_b = CRM_RcBase_Test_Utils::cvApi4Get('Contact', [], ["external_identifier=${external_id_b}"]);

        // Check if valid
        self::assertSame(
            $data_a[0],
            CRM_RcBase_Api_Get::contactData($contact_id_a),
            'Invalid contact data returned on valid contact ID.'
        );
        self::assertSame(
            $data_b[0],
            CRM_RcBase_Api_Get::contactData($contact_id_b),
            'Invalid contact data returned on valid contact ID.'
        );

        // Check for different
        self::assertNotSame($data_a, $data_b, 'Invalid contact data returned for different contact ID.');

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::contactData(\Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact')),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::contactData(0);
    }

    /**
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public function testGetContactDataWithInvalidId()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::contactData(-5);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetEmailId()
    {
        // Create contacts
        $contact_id = $this->individualCreate();

        // Create email
        $email = [
            'contact_id' => $contact_id,
            'email' => 'hannibal@senate.carthago',
            'location_type_id' => 3,
        ];
        $email_id = CRM_RcBase_Test_Utils::cvApi4Create('Email', $email);

        // Check valid email
        self::assertSame(
            $email_id,
            CRM_RcBase_Api_Get::emailID($contact_id, $email['location_type_id']),
            'Bad email ID returned'
        );

        // Check non-existent location type
        self::assertNull(
            CRM_RcBase_Api_Get::emailID($contact_id, \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::emailID(
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact'),
                $email['location_type_id']
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::emailID(-1, $email['location_type_id']);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetPhoneId()
    {
        // Create contacts
        $contact_id = $this->individualCreate();

        // Create phone
        $phone = [
            'contact_id' => $contact_id,
            'location_type_id' => 1,
            'phone' => '+36101234567',
        ];
        $phone_id = CRM_RcBase_Test_Utils::cvApi4Create('Phone', $phone);

        // Check valid phone
        self::assertSame(
            $phone_id,
            CRM_RcBase_Api_Get::phoneID($contact_id, $phone['location_type_id']),
            'Bad phone ID returned'
        );

        // Check non-existent location type
        self::assertNull(
            CRM_RcBase_Api_Get::phoneID($contact_id, \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::phoneID(
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact'),
                $phone['location_type_id']
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::phoneID(-5, $phone['location_type_id']);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetAddressId()
    {
        // Create contacts
        $contact_id = $this->individualCreate();

        // Create address
        $address = [
            'contact_id' => $contact_id,
            'location_type_id' => 1,
            'city' => 'Capua',
        ];
        $address_id = CRM_RcBase_Test_Utils::cvApi4Create('Address', $address);

        // Check valid address
        self::assertSame(
            $address_id,
            CRM_RcBase_Api_Get::addressID($contact_id, $address['location_type_id']),
            'Bad address ID returned'
        );

        // Check non-existent location type
        self::assertNull(
            CRM_RcBase_Api_Get::addressID($contact_id, \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::addressID(
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact'),
                $address['location_type_id']
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::addressID($contact_id, 0);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGetRelationshipId()
    {
        // Create contacts
        $contact_id = $this->individualCreate();
        $contact_id_other = $this->individualCreate();

        // Create relationship
        $relationship = [
            'contact_id_a' => $contact_id,
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
        ];
        $relationship_id = CRM_RcBase_Test_Utils::cvApi4Create('Relationship', $relationship);

        // Check valid relationship
        self::assertSame(
            $relationship_id,
            CRM_RcBase_Api_Get::relationshipID(
                $contact_id,
                $contact_id_other,
                $relationship['relationship_type_id']
            ),
            'Bad relationship ID returned'
        );

        // Check non-existent relationship type
        self::assertNull(
            CRM_RcBase_Api_Get::relationshipID(
                $contact_id,
                $contact_id,
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_relationship_type')
            ),
            'Not null returned on non-existent relationship type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::relationshipID(
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact'),
                $contact_id_other,
                $relationship['relationship_type_id']
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::relationshipID($contact_id, $contact_id_other, -5);
    }

    /**
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public function testGetDefaultLocationType()
    {
        $def_loc_type = (int)CRM_Core_BAO_LocationType::getDefault()->id;

        self::assertSame(
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
        // Create contacts
        $contact_id_source_a = $this->individualCreate();
        $contact_id_source_b = $this->individualCreate();
        $contact_id_target = $this->individualCreate();
        $contact_id_assignee_a = $this->individualCreate();
        $contact_id_assignee_b = $this->individualCreate();

        // Add activities
        $activity_a = [
            'activity_type_id' => 1,
            'source_contact_id' => $contact_id_source_a,
            'target_contact_id' => $contact_id_target,
            'assignee_contact_id' => $contact_id_assignee_a,
        ];
        $activity_b = [
            'activity_type_id' => 2,
            'source_contact_id' => $contact_id_source_a,
            'target_contact_id' => $contact_id_target,
            'assignee_contact_id' => $contact_id_assignee_a,
        ];

        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_a);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_a);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);

        // Add other activities
        $activity_a['source_contact_id'] = $contact_id_source_b;
        $activity_a['assignee_contact_id'] = $contact_id_assignee_b;

        $activity_b['assignee_contact_id'] = $contact_id_assignee_b;

        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_a);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);
        CRM_RcBase_Test_Utils::cvApi4Create('Activity', $activity_b);

        // Check activities when contact is target
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_target,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_TARGET
        );
        self::assertCount(9, $activities, 'Bad number of all activities when contact is the target');

        // Check activities when contact is target with filtering
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_target,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_TARGET,
            $activity_b['activity_type_id']
        );
        self::assertCount(6, $activities, 'Bad number of filtered activities when contact is the target');

        // Check non-existent activities returned
        $activities = CRM_RcBase_Api_Get::allActivity($contact_id_target, 5);
        self::assertCount(0, $activities, 'Non existent activities returned');

        // Check activities when contact is source
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_source_a,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_SOURCE
        );
        self::assertCount(8, $activities, 'Bad number of activities when contact is the source');

        // Check activities when contact is assignee
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_assignee_b,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_ASSIGNEE
        );
        self::assertCount(4, $activities, 'Bad number of activities when contact is the assignee');

        // Check activities when contact is assignee with filtering
        $activities = CRM_RcBase_Api_Get::allActivity(
            $contact_id_assignee_b,
            CRM_RcBase_Api_Get::ACTIVITY_RECORD_TYPE_ASSIGNEE,
            $activity_a['activity_type_id']
        );
        self::assertCount(1, $activities, 'Bad number of activities when contact is the assignee');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::allActivity($contact_id_target, 5, -5);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testContactHasTag()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create tag
        $tag = [
            'name' => 'Test tag',
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Add tag to contact
        $entity_tag = [
            'entity_table' => 'civicrm_contact',
            'entity_id' => $contact_id,
            'tag_id' => $tag_id,
        ];
        $entity_tag_id = CRM_RcBase_Test_Utils::cvApi4Create('EntityTag', $entity_tag);

        // Check valid tag
        self::assertSame(
            $entity_tag_id,
            CRM_RcBase_Api_Get::contactHasTag($contact_id, $tag_id),
            'Bad entity tag ID returned'
        );

        // Check non-existent tag
        self::assertNull(
            CRM_RcBase_Api_Get::contactHasTag($contact_id, \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_tag')),
            'Not null returned on non-existent tag'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::contactHasTag(
                \Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_contact'),
                $tag_id
            ),
            'Not null returned on non-existent contact ID'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::contactHasTag(-1, $tag_id);
    }

    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     */
    public function testParentTag()
    {
        // Create parent tag
        $tag = [
            'name' => 'Parent tag',
        ];
        $parent_tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Create child tag
        $tag = [
            'name' => 'Child tag',
            'parent_id' => $parent_tag_id,
        ];
        $child_tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Check tags
        self::assertSame($parent_tag_id, CRM_RcBase_Api_Get::parentTagId($child_tag_id), 'Bad parent tag ID returned for child');
        self::assertNull(CRM_RcBase_Api_Get::parentTagId($parent_tag_id), 'Not null returned for parent');

        // Check non-existent tag
        self::assertNull(
            CRM_RcBase_Api_Get::parentTagId(\Civi\RcBase\Utils\DB::getNextAutoIncrementValue('civicrm_tag')),
            'Not null returned for non-existent tag'
        );

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::parentTagId(-1);
    }

    /**
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    public function testSettingValue()
    {
        // Set value
        $setting_name = 'dateformatDatetime';
        $setting_value = '"%Y %B %E, %H:%M"';
        $result = cv(sprintf("api4 Setting.Set +v '%s=%s'", $setting_name, $setting_value));
        self::assertCount(1, $result, 'Bad number of results from cv');

        // Create Config object (this caches settings) and force a rebuild from DB
        CRM_Core_Config::singleton(true, true);

        self::assertSame($result[0]['value'], CRM_RcBase_Api_Get::settingValue($setting_name), 'Bad setting value returned');

        // Missing setting name
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Setting name missing');
        CRM_RcBase_Api_Get::settingValue('');
    }

    /**
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    public function testSettingValueWithContactId()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Set value
        $setting_name = 'resCacheCode';
        $setting_value = 'test-cache-code';
        civicrm_api4('Setting', 'set', [
            'values' => [$setting_name => $setting_value],
            'domainId' => 1,
            'contactId' => $contact_id,
        ]);

        // Check setting
        $result = civicrm_api4('Setting', 'get', [
            'select' => [$setting_name],
            'domainId' => 1,
            'contactId' => $contact_id,
        ]);
        self::assertCount(1, $result, 'Bad number of results');
        self::assertSame($setting_value, $result[0]['value'], 'Failed to set contact setting.');

        // Create Config object (this caches settings) and force a rebuild from DB
        CRM_Core_Config::singleton(true, true);

        self::assertSame($setting_value, CRM_RcBase_Api_Get::settingValue($setting_name, $contact_id), 'Bad setting value returned');

        // Invalid Domain
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid Domain ID');
        CRM_RcBase_Api_Get::settingValue('dateformatDatetime', null, -5);
    }

    /**
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    public function testSettingValueNonExistentSettingThrowsException()
    {
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Unknown settings');
        CRM_RcBase_Api_Get::settingValue('non-existent');
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGroupIdByName()
    {
        // Create group
        $group_data = [
            'title' => 'Placeholder group',
            'name' => 'place_holder_group',
        ];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);

        // Check valid group
        self::assertSame($group_id, CRM_RcBase_Api_Get::groupIDByName($group_data['name']), 'Bad group ID returned');

        // Check invalid
        self::assertNull(CRM_RcBase_Api_Get::groupIDByName('non-existent'), 'Bad group ID returned on non-existent group');
        self::assertNull(CRM_RcBase_Api_Get::groupIDByName(''), 'Bad group ID returned on empty group name');
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testGroupIdByTitle()
    {
        // Create group
        $group_data = [
            'title' => 'Test group by title',
        ];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);

        // Check valid group
        self::assertSame($group_id, CRM_RcBase_Api_Get::groupIDByTitle($group_data['title']), 'Bad group ID returned');

        // Check invalid
        self::assertNull(CRM_RcBase_Api_Get::groupIDByTitle('non-existent'), 'Bad group ID returned on non-existent group');
        self::assertNull(CRM_RcBase_Api_Get::groupIDByTitle(''), 'Bad group ID returned on empty group title');
    }

    /**
     * @throws UnauthorizedException|API_Exception
     * @throws CRM_Core_Exception
     */
    public function testTagIdByName()
    {
        // Create tag
        $tag_data = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag_data);

        // Check valid tag
        self::assertSame($tag_id, CRM_RcBase_Api_Get::tagIDByName($tag_data['name']), 'Bad tag ID returned');

        // Check invalid
        self::assertNull(CRM_RcBase_Api_Get::tagIDByName('non-existent'), 'Bad tag ID returned on non-existent tag');
        self::assertNull(CRM_RcBase_Api_Get::tagIDByName(''), 'Bad tag ID returned on empty tag name');
    }

    /**
     * @throws \CRM_Core_Exception
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testContactSubType()
    {
        // Create subtypes
        $sub_type_a = [
            'name' => 'individual_sub_type_a',
            'label' => 'Sub-Type A',
            'parent_id.name' => 'Individual',
        ];
        $sub_type_b = [
            'name' => 'individual_sub_type_b',
            'label' => 'Sub-Type B',
            'parent_id.name' => 'Individual',
        ];
        CRM_RcBase_Test_Utils::cvApi4Create('ContactType', $sub_type_a);
        CRM_RcBase_Test_Utils::cvApi4Create('ContactType', $sub_type_b);

        // Create contact - no subtype
        $contact_id = $this->individualCreate();
        $subtype = CRM_RcBase_Api_Get::contactSubType($contact_id);
        self::assertCount(0, $subtype, 'Wrong number of subtypes: should be zero');

        // Create contact - sub-type A
        $contact_id = $this->individualCreate(['contact_sub_type' => [$sub_type_a['name']],]);
        $subtype = CRM_RcBase_Api_Get::contactSubType($contact_id);
        self::assertCount(1, $subtype, 'Wrong number of subtypes: should be one');
        self::assertSame([$sub_type_a['name']], $subtype, 'Wrong subtype returned');

        // Create contact - sub-type A and B
        $contact_id = $this->individualCreate(['contact_sub_type' => [$sub_type_a['name'], $sub_type_b['name']],]);
        $subtype = CRM_RcBase_Api_Get::contactSubType($contact_id);
        self::assertCount(2, $subtype, 'Wrong number of subtypes: should be 2');
        self::assertSame([$sub_type_a['name'], $sub_type_b['name']], $subtype, 'Wrong subtypes returned');

        // Check invalid ID
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::contactSubType(-1);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testGroupContactStatus()
    {
        // Create group, contact
        $group_data = ['title' => 'Group contact test group',];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);
        $contact_id = $this->individualCreate();

        // Check new contact
        $result = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, $result, 'Wrong value returned for new contact');

        // Check non-existent group
        $result = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id + 1);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, $result, 'Wrong value returned for non-existent group');

        // Add contact to group
        $group_contact_id = CRM_RcBase_Test_Utils::cvApi4Create('GroupContact', ['group_id' => $group_id, 'contact_id' => $contact_id,]);
        $result = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, $result, 'Wrong value returned for added contact');

        // Set to pending
        GroupContact::update()
            ->addValue('status', 'Pending')
            ->addWhere('id', '=', $group_contact_id)
            ->execute();
        $result = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_PENDING, $result, 'Wrong value returned for pending contact');

        // Remove contact
        GroupContact::update()
            ->addValue('status', 'Removed')
            ->addWhere('id', '=', $group_contact_id)
            ->execute();
        $result = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, $result, 'Wrong value returned for removed contact');

        // Check invalid ID
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Get::groupContactStatus(-1, -1);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testInvalidGroupContactStatusThrowsException()
    {
        // Create group, contact
        $group_data = [
            'title' => 'Group contact invalid status test group',
        ];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);
        $contact_id = $this->individualCreate();

        // Add contact to group with invalid status
        CRM_RcBase_Test_Utils::cvApi4Create('GroupContact', ['group_id' => $group_id, 'contact_id' => $contact_id, 'status' => 'invalid',]);
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Invalid status returned');
        CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testOptionValue()
    {
        // Create activity
        $activity_data = [
            'option_group_id.name' => 'activity_type',
            'label' => 'test_activity',
            'name' => 'test_activity',
        ];
        $option_value_id = CRM_RcBase_Test_Utils::cvApi4Create('OptionValue', $activity_data);
        $activity_type = CRM_RcBase_Test_Utils::cvApi4Get('OptionValue', ['value'], ["id={$option_value_id}"]);
        $activity_type_id = $activity_type[0]['value'];

        self::assertSame($activity_type_id, CRM_RcBase_Api_Get::optionValue('activity_type', $activity_data['name']), 'Wrong option value returned');

        // Check invalid
        self::assertNull(CRM_RcBase_Api_Get::optionValue('activity_type', 'non-existent-activity-type'), 'Wrong option value returned on non-existent option');
        self::assertNull(CRM_RcBase_Api_Get::optionValue('activity_type', ''), 'Wrong option value returned on empty option name');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testGetSystemUser()
    {
        self::assertSame(PHPUnit::createLoggedInUser(), CRM_RcBase_Api_Get::systemUserContactID(), 'Wrong contact ID returned');
    }
}
