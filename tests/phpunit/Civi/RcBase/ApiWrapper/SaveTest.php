<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Utils\PHPUnit;

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
    public function testAddContactToGroup()
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
}
