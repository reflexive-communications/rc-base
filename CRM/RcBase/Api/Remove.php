<?php

use Civi\Api4\GroupContact;

/**
 * Common Remove Actions
 *
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Api_Remove
{
    /**
     * Remove contact from group
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return void
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function removeContactFromGroup(int $contact_id, int $group_id, bool $check_permissions = false): void
    {
        if ($contact_id < 1 || $group_id < 1) {
            throw new API_Exception('Invalid ID.');
        }

        $status = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id, $check_permissions);

        switch ($status) {
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE:
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED:
                return;
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_PENDING:
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED:
                $result = GroupContact::get($check_permissions)
                    ->addSelect('id')
                    ->addWhere('group_id', '=', $group_id)
                    ->addWhere('contact_id', '=', $contact_id)
                    ->setLimit(1)
                    ->execute();
                $group_contact_id = CRM_RcBase_Api_Get::parseResultsFirst($result, 'id');
                CRM_RcBase_Api_Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed',], $check_permissions);
                return;
            default:
                throw new API_Exception(sprintf('Invalid status returned: %s', $status));
        }
    }

    /**
     * Remove all contacts from a group
     *
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return void
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function emptyGroup(int $group_id, bool $check_permissions = false): void
    {
        if ($group_id < 1) {
            throw new API_Exception('Invalid ID.');
        }

        GroupContact::update($check_permissions)
            ->addValue('status', 'Removed')
            ->addWhere('group_id', '=', $group_id)
            ->execute();
    }
}
