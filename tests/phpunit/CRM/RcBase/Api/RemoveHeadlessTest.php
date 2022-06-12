<?php

use Civi\Api4\GroupContact;

/**
 * Test API Remove class
 *
 * @group headless
 */
class CRM_RcBase_Api_RemoveHeadlessTest extends CRM_RcBase_Api_ApiTestCase
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
        $group_data = ['title' => 'Group contact test group',];
        $group_id = CRM_RcBase_Test_Utils::cvApi4Create('Group', $group_data);
        $contact_id = $this->individualCreate();

        // Remove not added contact
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (not added)');

        // Add to group then remove
        $group_contact_id = CRM_RcBase_Api_Save::addContactToGroup($contact_id, $group_id);
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (added)');

        // Remove removed
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (removed)');

        // Change to pending then remove
        GroupContact::update()
            ->addValue('status', 'Pending')
            ->addWhere('id', '=', $group_contact_id)
            ->execute();
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, $group_id);
        self::assertSame(CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED, CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (pending)');

        // Invalid ID
        self::expectException(API_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        CRM_RcBase_Api_Remove::removeContactFromGroup($contact_id, -1);
    }
}
