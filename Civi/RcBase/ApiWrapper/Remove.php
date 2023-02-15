<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\GroupContact;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;

/**
 * Common Remove Actions
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Remove
{
    /**
     * Remove contact from group
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Number of affected contacts (1 if contact was in group before, 0 if wasn't)
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function removeContactFromGroup(int $contact_id, int $group_id, bool $check_permissions = false): int
    {
        if ($contact_id < 1 || $group_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $status = Get::groupContactStatus($contact_id, $group_id, $check_permissions);

        switch ($status) {
            case Get::GROUP_CONTACT_STATUS_NONE:
            case Get::GROUP_CONTACT_STATUS_REMOVED:
                return 0;
            case Get::GROUP_CONTACT_STATUS_PENDING:
            case Get::GROUP_CONTACT_STATUS_ADDED:
                $params = [
                    'select' => ['id'],
                    'where' => [
                        ['contact_id', '=', $contact_id],
                        ['group_id', '=', $group_id],
                    ],
                    'limit' => 1,
                ];
                $group_contact_id = Get::parseResultsFirst(Get::entity('GroupContact', $params, $check_permissions), 'id');
                Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed'], $check_permissions);

                return 1;
            default:
                throw new APIException('GroupContact', 'get', "Invalid status returned: {$status}");
        }
    }

    /**
     * Remove all contacts from a group
     *
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Number of removed contacts
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function emptyGroup(int $group_id, bool $check_permissions = false): int
    {
        if ($group_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $contacts = GroupContact::update($check_permissions)
            ->addValue('status', 'Removed')
            ->addWhere('group_id', '=', $group_id)
            ->addClause('OR', ['status', '=', 'Pending'], ['status', '=', 'Added'])
            ->execute();

        return count($contacts);
    }
}
