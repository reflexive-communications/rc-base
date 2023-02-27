<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Utils\PHPUnit;
use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class RemoveTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testRemoveEntity()
    {
        $contact_id = PHPUnit::createIndividual();
        Remove::entity('Contact', $contact_id);
        self::assertNull(Get::entityByID('Contact', $contact_id), 'Contact not deleted');

        // Non-existent ID (e.g. already deleted)
        self::expectException(APIException::class);
        self::expectExceptionMessage('Failed to delete entity');
        Remove::entity('Contact', $contact_id);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testRemoveEntityWithInvalidIdThrowsException()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('ID must be positive');
        Remove::entity('Contact', -5);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testRemoveEntityWithInvalidEntityThrowsException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('API (InvalidEntityName, delete) does not exist');
        Remove::entity('InvalidEntityName', 5);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testRemoveContactFromGroup()
    {
        // Create group, contact
        $group_id = Create::group(['title' => 'Group contact test group']);
        $contact_id = PHPUnit::createIndividual();

        // Remove not added contact
        self::assertSame(0, Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (not added)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (not added)');
        // Add to group then remove
        $group_contact_id = Save::addContactToGroup($contact_id, $group_id);
        self::assertSame(1, Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (added)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (added)');
        // Remove removed
        self::assertSame(0, Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (removed)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (removed)');
        // Change to pending then remove
        Update::entity('GroupContact', $group_contact_id, ['status' => 'Pending']);
        self::assertSame(1, Remove::removeContactFromGroup($contact_id, $group_id), 'Wrong number of affected contacts (pending)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id, $group_id), 'Failed to remove contact (pending)');

        // Invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Remove::removeContactFromGroup($contact_id, -1);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testEmptyGroup()
    {
        // Create groups, contacts
        $group_id_a = Create::group(['title' => 'Group A']);
        $group_id_b = Create::group(['title' => 'Group B']);
        $contact_id_a = PHPUnit::createIndividual();
        $contact_id_b = PHPUnit::createIndividual();
        $contact_id_c = PHPUnit::createIndividual();
        $contact_id_d = PHPUnit::createIndividual();

        // Add contacts to group A with different status
        Create::entity('GroupContact', [
            'group_id' => $group_id_a,
            'contact_id' => $contact_id_a,
            'status' => 'Added',
        ]);
        Create::entity('GroupContact', [
            'group_id' => $group_id_a,
            'contact_id' => $contact_id_b,
            'status' => 'Removed',
        ]);
        Create::entity('GroupContact', [
            'group_id' => $group_id_a,
            'contact_id' => $contact_id_c,
            'status' => 'Pending',
        ]);

        // Add contacts to group B with different status
        Create::entity('GroupContact', [
            'group_id' => $group_id_b,
            'contact_id' => $contact_id_a,
            'status' => 'Added',
        ]);
        Create::entity('GroupContact', [
            'group_id' => $group_id_b,
            'contact_id' => $contact_id_b,
            'status' => 'Removed',
        ]);
        Create::entity('GroupContact', [
            'group_id' => $group_id_b,
            'contact_id' => $contact_id_c,
            'status' => 'Pending',
        ]);

        $contacts = Remove::emptyGroup($group_id_a);
        self::assertSame(2, $contacts, 'Wrong number of removed contacts');

        // Check group A
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id_a, $group_id_a), 'Failed to empty group (added)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id_b, $group_id_a), 'Failed to empty group (removed)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id_c, $group_id_a), 'Failed to empty group (pending)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id_d, $group_id_a), 'Failed to empty group (no history)');

        // Check group B
        self::assertSame(Get::GROUP_CONTACT_STATUS_ADDED, Get::groupContactStatus($contact_id_a, $group_id_b), 'Failed to empty group (added)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_REMOVED, Get::groupContactStatus($contact_id_b, $group_id_b), 'Failed to empty group (removed)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_PENDING, Get::groupContactStatus($contact_id_c, $group_id_b), 'Failed to empty group (pending)');
        self::assertSame(Get::GROUP_CONTACT_STATUS_NONE, Get::groupContactStatus($contact_id_d, $group_id_b), 'Failed to empty group (no history)');

        // Empty already empty group
        $contacts = Remove::emptyGroup($group_id_a);
        self::assertSame(0, $contacts, 'Wrong number of removed contacts');

        // Invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Remove::emptyGroup(-1);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testRemoveTagFromContact()
    {
        // Create contact, tag add tag to contact
        $contact_id = PHPUnit::createIndividual();
        $tag_id = Create::tag(['name' => 'Test tag']);
        Save::tagContact($contact_id, $tag_id);

        // Remove tag
        self::assertSame(1, Remove::tagFromContact($contact_id, $tag_id), 'Contact was not affected');
        self::assertNull(Get::contactHasTag($contact_id, $tag_id), 'Tag not removed');
        // Remove tag from untagged contact
        self::assertSame(0, Remove::tagFromContact($contact_id, $tag_id), 'Contact was affected');
        self::assertNull(Get::contactHasTag($contact_id, $tag_id), 'Tag not removed');
    }
}
