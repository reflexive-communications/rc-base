<?php

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Setting;

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
        $contact_id_a = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_b = $this->individualCreate([], self::getNextContactSequence());

        // Create emails
        $email_a = [
            'contact_id' => $contact_id_a,
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_id_a = CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_a);
        $email_b = [
            'contact_id' => $contact_id_a,
            'email' => 'ceasar@home.rome',
            'location_type_id' => 2,
        ];
        $email_id_b = CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_b);
        $email_c = [
            'contact_id' => $contact_id_b,
            'email' => 'antonius@senate.rome',
            'location_type_id' => 1,
        ];
        $email_id_c = CRM_RcBase_Test_Utils::cvApi4Create('Email', $email_c);

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

        $contact_id_a = $this->individualCreate(
            ['external_identifier' => $external_id_a],
            self::getNextContactSequence()
        );
        $contact_id_b = $this->individualCreate(
            ['external_identifier' => $external_id_b],
            self::getNextContactSequence()
        );

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

        $contact_id_a = $this->individualCreate(
            ['external_identifier' => $external_id_a],
            self::getNextContactSequence()
        );
        $contact_id_b = $this->individualCreate(
            ['external_identifier' => $external_id_b],
            self::getNextContactSequence()
        );

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
            CRM_RcBase_Api_Get::contactData(CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact')),
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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

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
            CRM_RcBase_Api_Get::emailID($contact_id, CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::emailID(
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact'),
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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

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
            CRM_RcBase_Api_Get::phoneID($contact_id, CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::phoneID(
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact'),
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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

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
            CRM_RcBase_Api_Get::addressID($contact_id, CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_location_type')),
            'Not null returned on non-existent location type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::addressID(
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact'),
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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_other = $this->individualCreate([], self::getNextContactSequence());

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
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_relationship_type')
            ),
            'Not null returned on non-existent relationship type ID'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::relationshipID(
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact'),
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
        $contact_id_source_a = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_source_b = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_target = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_assignee_a = $this->individualCreate([], self::getNextContactSequence());
        $contact_id_assignee_b = $this->individualCreate([], self::getNextContactSequence());

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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

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
            CRM_RcBase_Api_Get::contactHasTag($contact_id, CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_tag')),
            'Not null returned on non-existent tag'
        );

        // Check non-existent contact ID
        self::assertNull(
            CRM_RcBase_Api_Get::contactHasTag(
                CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_contact'),
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
            CRM_RcBase_Api_Get::parentTagId(CRM_RcBase_Test_Utils::getNextAutoIncrementValue('civicrm_tag')),
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
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Set value
        $setting_name = 'resCacheCode';
        $setting_value = 'test-cache-code';
        Setting::set()
            ->addValue($setting_name, $setting_value)
            ->setDomainId(1)
            ->setContactId($contact_id)
            ->execute();

        // Check setting
        $result = Setting::get()
            ->addSelect($setting_name)
            ->setDomainId(1)
            ->setContactId($contact_id)
            ->execute();
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
    public function testGetGroupIdByName()
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
    public function testGetGroupIdByTitle()
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
    public function testGetTagIdByName()
    {
        // Create tag
        $tag_data = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag_data);

        // Check valid group
        self::assertSame($tag_id, CRM_RcBase_Api_Get::tagIDByName($tag_data['name']), 'Bad tag ID returned');

        // Check invalid
        self::assertNull(CRM_RcBase_Api_Get::tagIDByName('non-existent'), 'Bad tag ID returned on non-existent tag');
        self::assertNull(CRM_RcBase_Api_Get::tagIDByName(''), 'Bad tag ID returned on empty tag name');
    }
}
