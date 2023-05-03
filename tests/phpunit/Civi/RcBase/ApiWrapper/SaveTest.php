<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Utils\PHPUnit;
use CRM_Contact_BAO_GroupContactCache;

/**
 * @group headless
 */
class SaveTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testSaveTagWithNotTaggedContact()
    {
        // Create contact and tag
        $contact_id = PHPUnit::createIndividual();
        $tag_id = Create::tag(['name' => 'Test tag']);

        // Save tag to contact
        $entity_tag_id = Save::tagContact($contact_id, $tag_id);

        self::assertSame(Get::contactHasTag($contact_id, $tag_id), $entity_tag_id, 'Wrong entity tag ID returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testSaveTagWithTaggedContact()
    {
        // Create contact and tag
        $contact_id = PHPUnit::createIndividual();
        $tag_id = Create::tag(['name' => 'Test tag 2']);
        $entity_tag_id_create = Create::tagContact($contact_id, $tag_id);

        // Save tag to contact
        $entity_tag_id_save = Save::tagContact($contact_id, $tag_id);
        self::assertSame($entity_tag_id_create, $entity_tag_id_save, 'Wrong entity tag ID returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAddSubTypeToContact()
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

        // Add subtype A
        Save::addSubTypeToContact($contact_id, [$sub_type_a['name']]);
        $subtype = Get::contactSubType($contact_id);
        self::assertCount(1, $subtype, 'Wrong number of subtypes');
        self::assertSame([$sub_type_a['name']], $subtype, 'Wrong subtype returned');

        // Add subtype B too
        Save::addSubTypeToContact($contact_id, [$sub_type_b['name']]);
        $subtype = Get::contactSubType($contact_id);
        self::assertCount(2, $subtype, 'Wrong number of subtypes');
        self::assertSame([$sub_type_a['name'], $sub_type_b['name']], $subtype, 'Wrong subtypes returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testAddContactToNormalGroup()
    {
        // Create group, contact
        $group_data = ['title' => 'Group contact test group'];
        $group_id = Create::group($group_data);
        $contact_id = PHPUnit::createIndividual();

        // Add contact to group
        $group_contact_id_original = Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (new)');

        // Add again
        $group_contact_id = Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (added)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (added)');

        // Set to pending then add
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Pending']);
        $group_contact_id = Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (pending)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (pending)');

        // Remove contact then add
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed']);
        $group_contact_id = Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (removed)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (removed)');

        // Non-existent group
        self::expectException(APIException::class);
        self::expectExceptionMessage('DB Error: constraint violation');
        Save::addContactToGroup($contact_id, $group_id + 1);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testAddContactToSmartGroup()
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

        // Add contact manually to group (already in by search)
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');
        Save::addContactToGroup($contact_id, $group_id_all);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id_all), 'Failed to add contact (new)');

        // Add contact manually to group (not present yet) - don't update cache
        Save::addContactToGroup($contact_id, $group_id_nobody);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id_nobody), 'Failed to add contact (added)');
        // Check cache still holds old group membership
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(1, $groups_cache['group'], 'Contact not in single smart group');
        self::assertEquals($group_id_all, $groups_cache['group'][0]['id'], 'Contact in wrong smart group');

        // Add contact again - this time update cache
        $group_contact_id = Save::addContactToGroup($contact_id, $group_id_nobody, false, true);
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id, $group_id_nobody), 'Failed to add contact (added)');
        // Check cache has real group membership
        $groups_cache = CRM_Contact_BAO_GroupContactCache::contactGroup($contact_id);
        self::assertCount(2, $groups_cache['group'], 'Contact not in two smart groups');

        // Check invalid status
        Update::entity('GroupContact', $group_contact_id, ['status' => 'invalid']);
        self::expectException(APIException::class);
        self::expectExceptionMessage('Invalid status returned');
        Save::addContactToGroup($contact_id, $group_id_nobody);
    }
}
