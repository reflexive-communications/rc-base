<?php

use Civi\Api4\GroupContact;

/**
 * @group headless
 */
class CRM_RcBase_Api_SaveTest extends CRM_RcBase_Api_ApiTestCase
{
    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSaveTagWithNotTaggedContact()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create tag
        $tag = [
            'name' => 'Test tag',
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        // Save tag to contact
        $entity_tag_id = CRM_RcBase_Api_Save::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        self::assertCount(
            count($all_entity_tag_old) + 1,
            $all_entity_tag_new,
            'No new entity tag created'
        );

        // Get from DB
        $id = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id'], [
            'entity_table=civicrm_contact',
            "entity_id=${contact_id}",
            "tag_id=${tag_id}",
        ]);
        self::assertCount(1, $id, 'Not one result returned for "id"');

        // Check valid ID
        self::assertSame($id[0]['id'], $entity_tag_id, 'Bad entity tag ID returned');
    }

    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSaveTagWithTaggedContact()
    {
        // Create contact
        $contact_id = $this->individualCreate();

        // Create tag
        $tag = [
            'name' => 'Another test tag',
        ];
        $tag_id = CRM_RcBase_Test_Utils::cvApi4Create('Tag', $tag);

        // Add tag to contact
        $entity_tag = [
            'entity_table' => 'civicrm_contact',
            'entity_id' => $contact_id,
            'tag_id' => $tag_id,
        ];
        $entity_tag_id = CRM_RcBase_Test_Utils::cvApi4Create('EntityTag', $entity_tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        // Save tag to contact
        $entity_tag_id_save = CRM_RcBase_Api_Save::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = CRM_RcBase_Test_Utils::cvApi4Get('EntityTag', ['id']);

        self::assertCount(
            count($all_entity_tag_old),
            $all_entity_tag_new,
            'Additional entity tag created'
        );
        // Check valid ID
        self::assertNull($entity_tag_id_save, 'Not null returned when no tagging was needed');
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
        CRM_RcBase_Test_Utils::cvApi4Create('ContactType', $sub_type_a);
        CRM_RcBase_Test_Utils::cvApi4Create('ContactType', $sub_type_b);

        // Create contact - no subtype
        $contact_id = $this->individualCreate();
        $contact_data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_sub_type'],
            ["id=${contact_id}"]
        );
        self::assertCount(1, $contact_data, 'Wrong number of contacts returned');
        self::assertNull($contact_data[0]['contact_sub_type'], 'Wrong subtypes returned');

        // Add subtype A
        CRM_RcBase_Api_Save::addSubTypeToContact($contact_id, [$sub_type_a['name']]);
        $contact_data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_sub_type'],
            ["id=${contact_id}"]
        );
        self::assertCount(1, $contact_data, 'Wrong number of contacts returned');
        self::assertSame([$sub_type_a['name']], $contact_data[0]['contact_sub_type'], 'Wrong subtypes returned');

        // Add subtype B too
        CRM_RcBase_Api_Save::addSubTypeToContact($contact_id, [$sub_type_b['name']]);
        $contact_data = CRM_RcBase_Test_Utils::cvApi4Get(
            'Contact',
            ['contact_sub_type'],
            ["id=${contact_id}"]
        );
        self::assertCount(1, $contact_data, 'Wrong number of contacts returned');
        self::assertSame([$sub_type_a['name'], $sub_type_b['name']], $contact_data[0]['contact_sub_type'], 'Wrong subtypes returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAddContactToGroup()
    {
        // Create group, contact
        $group_data = ['title' => 'Group contact test group'];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);
        $contact_id = $this->individualCreate();

        // Add contact to group
        $group_contact_id_original = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (new)');

        // Add again
        $group_contact_id = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (added)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (added)');

        // Set to pending then add
        GroupContact::update()
            ->addValue('status', 'Pending')
            ->addWhere('id', '=', $group_contact_id_original)
            ->execute();
        $group_contact_id = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (pending)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (pending)');

        // Remove contact then add
        GroupContact::update()
            ->addValue('status', 'Removed')
            ->addWhere('id', '=', $group_contact_id_original)
            ->execute();
        $group_contact_id = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to add contact (removed)');
        self::assertSame($group_contact_id_original, $group_contact_id, 'Group contact ID has changed (removed)');

        // Non-existent group
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('DB Error: constraint violation');
        CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id + 1);
    }
}