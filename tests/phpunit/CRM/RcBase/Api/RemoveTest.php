<?php

use Civi\Api4\GroupContact;

/**
 * @group headless
 */
class CRM_RcBase_Api_RemoveTest extends CRM_RcBase_Api_ApiTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testRemoveContactFromGroup()
    {
        // Create group, contact
        $group_data = ['title' => 'Group contact test group'];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);
        $contact_id = $this->individualCreate();

        // Remove not added contact
        self::assertSame(0, CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (not added)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (not added)');

        // Add to group then remove
        $group_contact_id = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(1, CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (added)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (added)');

        // Remove removed
        self::assertSame(0, CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (removed)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (removed)');

        // Change to pending then remove
        GroupContact::update()
            ->addValue('status', 'Pending')
            ->addWhere('id', '=', $group_contact_id)
            ->execute();
        self::assertSame(1, CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (pending)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (pending)');

        // Invalid ID
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, -1);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testEmptyGroup()
    {
        // Create groups, contacts
        $group_a = ['title' => 'Group A'];
        $group_b = ['title' => 'Group B'];
        $group_id_a = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_a);
        $group_id_b = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_b);
        $contact_id_a = $this->individualCreate();
        $contact_id_b = $this->individualCreate();
        $contact_id_c = $this->individualCreate();
        $contact_id_d = $this->individualCreate();

        // Add contacts to group A with different status
        GroupContact::create()
            ->addValue('group_id', $group_id_a)
            ->addValue('contact_id', $contact_id_a)
            ->addValue('status', 'Added')
            ->execute();
        GroupContact::create()
            ->addValue('group_id', $group_id_a)
            ->addValue('contact_id', $contact_id_b)
            ->addValue('status', 'Removed')
            ->execute();
        GroupContact::create()
            ->addValue('group_id', $group_id_a)
            ->addValue('contact_id', $contact_id_c)
            ->addValue('status', 'Pending')
            ->execute();

        // Add contacts to group B with different status
        GroupContact::create()
            ->addValue('group_id', $group_id_b)
            ->addValue('contact_id', $contact_id_a)
            ->addValue('status', 'Added')
            ->execute();
        GroupContact::create()
            ->addValue('group_id', $group_id_b)
            ->addValue('contact_id', $contact_id_b)
            ->addValue('status', 'Removed')
            ->execute();
        GroupContact::create()
            ->addValue('group_id', $group_id_b)
            ->addValue('contact_id', $contact_id_c)
            ->addValue('status', 'Pending')
            ->execute();

        $contacts = CRM_RcBase_Api_Remove::emptyGroup($group_id_a);
        self::assertSame(2, $contacts, 'Wrong number of removed contacts');

        // Check group A
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id_a, $group_id_a), 'Failed to empty group (added)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id_b, $group_id_a), 'Failed to empty group (removed)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id_c, $group_id_a), 'Failed to empty group (pending)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, CRM_RcBase_Api_Get::groupContactStatus($contact_id_d, $group_id_a), 'Failed to empty group (no history)');

        // Check group B
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED, CRM_RcBase_Api_Get::groupContactStatus($contact_id_a, $group_id_b), 'Failed to empty group (added)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id_b, $group_id_b), 'Failed to empty group (removed)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_PENDING, CRM_RcBase_Api_Get::groupContactStatus($contact_id_c, $group_id_b), 'Failed to empty group (pending)');
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, CRM_RcBase_Api_Get::groupContactStatus($contact_id_d, $group_id_b), 'Failed to empty group (no history)');

        // Empty already empty group
        $contacts = CRM_RcBase_Api_Remove::emptyGroup($group_id_a);
        self::assertSame(0, $contacts, 'Wrong number of removed contacts');

        // Invalid ID
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Remove::emptyGroup(-1);
    }
}
