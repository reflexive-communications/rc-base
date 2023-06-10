<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Utils\DB;
use Civi\RcBase\Utils\PHPUnit;
use CRM_Contact_BAO_GroupContactCache;
use CRM_Core_BAO_LocationType;

/**
 * @group headless
 */
class GetTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntity()
    {
        $api_key = 'get_contact_api_key';
        $contact_id = PHPUnit::createIndividual(0, ['api_key' => $api_key]);

        $results = Get::entity('Contact', ['where' => [['api_key', '=', $api_key]]]);
        self::assertSame('Contact', $results->entity, 'Wrong entity called');
        self::assertSame('get', $results->action, 'Wrong action called');
        self::assertCount(1, $results, 'Contact not found');
        self::assertArrayHasKey('id', $results[0], 'id not returned');
        self::assertSame($contact_id, $results[0]['id'], 'Wrong contact returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityInvalidEntityThrowsException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('API (NonExistent, get) does not exist');
        Get::entity('NonExistent');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testParseResultFirst()
    {
        $counter = PHPUnit::nextCounter();
        PHPUnit::createIndividual($counter);

        // Check existent record
        $results = Get::entity('Contact', ['where' => [['external_identifier', '=', "ext_{$counter}"]]]);
        self::assertGreaterThan(30, count(Get::parseResultsFirst($results)), 'Not all fields returned: For a contact entity at least 30 fields is expected');
        self::assertSame("ext_{$counter}", Get::parseResultsFirst($results, 'external_identifier'), 'external_identifier not returned as string');

        // Check non-existent record
        $results = Get::entity('Contact', ['where' => [['external_identifier', '=', 'non-existent']]]);
        self::assertNull(Get::parseResultsFirst($results), 'Not null returned on non-existent record');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testParseResultFirstNonExistentFieldThrowsException()
    {
        PHPUnit::createIndividual();
        $results = Get::entity('Contact');

        self::expectException(APIException::class);
        self::expectExceptionMessage('non_existent_field not found');
        Get::parseResultsFirst($results, 'non_existent_field');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityById()
    {
        $values = [
            'name' => 'test_entity_tag',
            'description' => 'This is a test tag',
        ];
        $id = Create::tag($values);

        // Check all fields
        $data = Get::entityByID('Tag', $id);
        self::assertArrayHasKey('name', $data, 'name missing');
        self::assertArrayHasKey('description', $data, 'description missing');
        self::assertSame($values['name'], $data['name'], 'Wrong name');
        self::assertSame($values['description'], $data['description'], 'Wrong description');
        self::assertGreaterThan(2, count($data), 'Not all fields returned: for a Tag entity at least 3 fields is expected');

        // Check single field
        self::assertSame($values['description'], Get::entityByID('Tag', $id, 'description'), 'description not returned as string');

        // Check two fields
        $data = Get::entityByID('Tag', $id, ['id', 'description']);
        self::assertArrayHasKey('id', $data, 'id missing');
        self::assertArrayHasKey('description', $data, 'description missing');
        self::assertCount(2, $data, 'Not exactly two fields returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityByName()
    {
        $values = [
            'title' => 'User friendly title',
            'name' => 'group_machine_name',
        ];
        $id = Create::entity('Group', $values);

        // Check all fields
        $data = Get::entityByName('Group', $values['name']);
        self::assertArrayHasKey('id', $data, 'id missing');
        self::assertArrayHasKey('title', $data, 'title missing');
        self::assertSame($id, $data['id'], 'Wrong id');
        self::assertSame($values['title'], $data['title'], 'Wrong title');
        self::assertGreaterThan(2, count($data), 'Not all fields returned: for a Group entity at least 3 fields is expected');

        // Check single field
        self::assertSame($values['title'], Get::entityByName('Group', $values['name'], 'title'), 'title not returned as string');

        // Check two fields
        $data = Get::entityByName('Group', $values['name'], ['id', 'title']);
        self::assertArrayHasKey('id', $data, 'id missing');
        self::assertArrayHasKey('title', $data, 'title missing');
        self::assertCount(2, $data, 'Not exactly two fields returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testGetContactIdByEmail()
    {
        // Create contacts
        $contact_id_a = PHPUnit::createIndividual();
        $contact_id_b = PHPUnit::createIndividual();

        // Create emails
        $email_a = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_b = [
            'email' => 'ceasar@home.rome',
            'location_type_id' => 2,
        ];
        $email_c = [
            'email' => 'antonius@senate.rome',
            'location_type_id' => 1,
        ];
        Create::email($contact_id_a, $email_a);
        Create::email($contact_id_a, $email_b);
        Create::email($contact_id_b, $email_c);

        // Check valid email
        self::assertSame($contact_id_a, Get::contactIDByEmail($email_a['email']), 'Wrong contact ID returned');
        self::assertSame($contact_id_a, Get::contactIDByEmail($email_b['email']), 'Wrong contact ID returned');
        self::assertSame($contact_id_b, Get::contactIDByEmail($email_c['email']), 'Wrong contact ID returned');
        // Check empty email
        self::assertNull(Get::contactIDByEmail(''), 'Not null returned on empty email');
        // Check non-existent email
        self::assertNull(Get::contactIDByEmail('nonexistent@rome.com'), 'Not null returned on non-existent email');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetSystemUser()
    {
        self::assertSame(PHPUnit::createLoggedInUser(), Get::systemUserContactID(), 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetDefaultLocationType()
    {
        $def_loc_type = (int)CRM_Core_BAO_LocationType::getDefault()->id;

        self::assertSame($def_loc_type, Get::defaultLocationTypeID(), 'Wrong default location type ID returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testContactHasTag()
    {
        // Create contact and tag
        $contact_id_tagged = PHPUnit::createIndividual();
        $contact_id_untagged = PHPUnit::createIndividual();
        $tag_id = Create::tag(['name' => 'Test tag']);
        $entity_tag_id = Create::tagContact($contact_id_tagged, $tag_id);

        self::assertSame($entity_tag_id, Get::contactHasTag($contact_id_tagged, $tag_id), 'Wrong entity tag ID returned');
        self::assertNull(Get::contactHasTag($contact_id_untagged, $tag_id), 'Not null returned on non-tagged contact');
        self::assertNull(Get::contactHasTag($contact_id_tagged, DB::getNextAutoIncrementValue('civicrm_tag')), 'Not null returned on non-existent tag');
        self::assertNull(Get::contactHasTag(DB::getNextAutoIncrementValue('civicrm_contact'), $tag_id), 'Not null returned on non-existent contact ID');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Get::contactHasTag(-1, $tag_id);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testParentTag()
    {
        // Create tags
        $parent_tag_id = Create::tag(['name' => 'Parent tag']);
        $child_tag_id = Create::tag(['name' => 'Child tag', 'parent_id' => $parent_tag_id]);

        // Check tags
        self::assertSame($parent_tag_id, Get::parentTagId($child_tag_id), 'Wrong parent tag ID returned for child');
        self::assertNull(Get::parentTagId($parent_tag_id), 'Not null returned for parent');
        // Check non-existent tag
        self::assertNull(Get::parentTagId(DB::getNextAutoIncrementValue('civicrm_tag')), 'Not null returned for non-existent tag');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Get::parentTagId(-1);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
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
        Create::entity('ContactType', $sub_type_a);
        Create::entity('ContactType', $sub_type_b);

        // Create contact - no subtype
        $contact_id = PHPUnit::createIndividual();
        $subtype = Get::contactSubType($contact_id);
        self::assertCount(0, $subtype, 'Wrong number of subtypes');

        // Create contact - sub-type A
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), ['contact_sub_type' => [$sub_type_a['name']]]);
        $subtype = Get::contactSubType($contact_id);
        self::assertCount(1, $subtype, 'Wrong number of subtypes');
        self::assertSame([$sub_type_a['name']], $subtype, 'Wrong subtype returned');

        // Create contact - sub-type A and B
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), ['contact_sub_type' => [$sub_type_a['name'], $sub_type_b['name']]]);
        $subtype = Get::contactSubType($contact_id);
        self::assertCount(2, $subtype, 'Wrong number of subtypes');
        self::assertSame([$sub_type_a['name'], $sub_type_b['name']], $subtype, 'Wrong subtypes returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Get::contactSubType(-1);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testGroupContactStatusWithNormalGroup()
    {
        // Create group, contact
        $group_data = ['title' => 'Group contact test group'];
        $group_id = Create::group($group_data);
        $contact_id = PHPUnit::createIndividual();

        // Check new contact
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id, $group_id), 'Wrong value returned for new contact');
        // Check non-existent group
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id, $group_id + 1), 'Wrong value returned for non-existent group');
        // Add contact to group
        $group_contact_id = Create::entity('GroupContact', ['group_id' => $group_id, 'contact_id' => $contact_id]);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id), 'Wrong value returned for added contact');
        // Set to pending
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Pending']);
        self::assertSame(Get::GROUP_CONTACT_STATUS_PENDING, Get::groupContactStatus($contact_id, $group_id), 'Wrong value returned for pending contact');
        // Remove contact
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed']);
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id, $group_id), 'Wrong value returned for removed contact');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Get::groupContactStatus(-1, -1);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testGroupContactStatusWithSmartGroup()
    {
        // Create smart groups, contact
        $saved_search_id_all = Create::entity('SavedSearch');
        $saved_search_id_nobody = Create::entity('SavedSearch', [
            'api_entity' => 'Contact',
            'api_params' => [
                'version' => 4,
                'where' => [['first_name', '=', 'nobody']],
            ],
        ]);
        $group_id_all = Create::group([
            'title' => 'Smart group with all contacts',
            'saved_search_id' => $saved_search_id_all,
        ]);
        $group_id_nobody = Create::group([
            'title' => 'Smart group with no contacts',
            'saved_search_id' => $saved_search_id_nobody,
        ]);
        $contact_id = PHPUnit::createIndividual();

        // Check new contact not in group
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id, $group_id_nobody, false, true), 'Wrong value returned for new contact when contact not in group');

        // Check new contact (added to group by search)
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id_all, false, true), 'Wrong value returned for new contact when contact in group');

        // Add contact to group manually
        $group_contact_id = Create::entity('GroupContact', ['group_id' => $group_id_all, 'contact_id' => $contact_id]);
        CRM_Contact_BAO_GroupContactCache::invalidateGroupContactCache($group_id_all);
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id_all, false, true), 'Wrong value returned for added contact');

        // Set to pending
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Pending']);
        CRM_Contact_BAO_GroupContactCache::invalidateGroupContactCache($group_id_all);
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');
        self::assertSame(Get::GROUP_CONTACT_STATUS_PENDING, Get::groupContactStatus($contact_id, $group_id_all, false, true), 'Wrong value returned for pending contact');

        // Remove contact
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed']);
        CRM_Contact_BAO_GroupContactCache::invalidateGroupContactCache($group_id_all);
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertSame([], $groups_cache, 'Contact not removed from smart group');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id, $group_id_all, false, true), 'Wrong value returned for removed contact');

        // Check invalid status
        Update::entity('GroupContact', $group_contact_id, ['status' => 'invalid']);
        self::expectException(APIException::class);
        self::expectExceptionMessage('Invalid status returned');
        Get::groupContactStatus($contact_id, $group_id_all);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testOptionValue()
    {
        // Create activity type
        $activity_data = [
            'label' => 'test_activity',
            'name' => 'test_activity',
        ];
        $activity_type_id = Create::optionValue('activity_type', $activity_data);

        self::assertSame($activity_type_id, Get::optionValue('activity_type', $activity_data['name']), 'Wrong option value returned');

        // Check invalid
        self::assertNull(Get::optionValue('activity_type', 'non-existent-activity-type'), 'Wrong option value returned on non-existent option');
        self::assertNull(Get::optionValue('activity_type', ''), 'Wrong option value returned on empty option name');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testLastOptionValue()
    {
        $last_value_before_insert = Get::lastOptionValue('group_type');

        // Add new option
        $group_type_id = Create::optionValue('group_type', [
            'label' => 'test_group_type',
            'name' => 'test_group_type',
        ]);

        self::assertEquals($group_type_id - 1, $last_value_before_insert, 'Wrong option value before insert');
        self::assertSame($group_type_id, Get::lastOptionValue('group_type'), 'Wrong option value after insert');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testContributionIdByTransactionId()
    {
        $transaction_id = 'test-trxn-01';
        $contact_id = PHPUnit::createIndividual();

        // Check non-existent, empty
        self::assertNull(Get::contributionIDByTransactionID($transaction_id), 'Not null returned on non-existent transaction ID');
        self::assertNull(Get::contributionIDByTransactionID(''), 'Not null returned on empty transaction ID');

        // Create contribution
        $contribution_id = Create::contribution($contact_id, [
            'trxn_id' => $transaction_id,
            'financial_type_id' => 1,
            'total_amount' => 5,
        ]);
        self::assertSame($contribution_id, Get::contributionIDByTransactionID($transaction_id), 'Wrong contribution ID returned');
    }
}
