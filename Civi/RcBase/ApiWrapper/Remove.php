<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\GroupContact;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Utils\DB;
use CRM_Contact_BAO_GroupContactCache;
use Throwable;

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
     * Delete entity
     *
     * @param string $entity Name of entity
     * @param int $entity_id Entity ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function entity(string $entity, int $entity_id, bool $check_permissions = false): void
    {
        if ($entity_id < 1) {
            throw new InvalidArgumentException('entity ID', 'ID must be positive');
        }

        $params = [
            'where' => [['id', '=', $entity_id]],
            'limit' => 1,
            'checkPermissions' => $check_permissions,
        ];
        if ($entity == 'Contact') {
            // Bring it on, delete contacts permanently
            $params['useTrash'] = false;
        }

        try {
            $results = civicrm_api4($entity, 'delete', $params);
        } catch (Throwable $ex) {
            throw new APIException($entity, 'delete', $ex->getMessage(), $ex);
        }

        if (count($results) < 1) {
            throw new APIException($entity, 'delete', 'Failed to delete entity');
        }
    }

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
     * @deprecated use \Civi\RcBase\ApiWrapper\Remove::contactFromGroup()
     */
    public static function removeContactFromGroup(int $contact_id, int $group_id, bool $check_permissions = false): int
    {
        if ($contact_id < 1 || $group_id < 1) {
            throw new InvalidArgumentException('ID', 'must be positive');
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
                $group_contact_id = Get::entitySingle('GroupContact', $params, 'id', $check_permissions);
                Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed'], $check_permissions);

                return 1;
            default:
                throw new APIException('GroupContact', 'get', "Invalid status returned: {$status}");
        }
    }

    /**
     * Remove contact from group
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     * @param bool $smart_group *DEPRECATED** Update group_contact_cache also
     *
     * @return int Number of affected contacts (1 if contact was in group before, 0 if wasn't)
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @todo Change signature in v2: remove $smart_group
     */
    public static function contactFromGroup(int $contact_id, int $group_id, bool $check_permissions = false, bool $smart_group = false): int
    {
        if ($contact_id < 1 || $group_id < 1) {
            throw new InvalidArgumentException('ID', 'must be positive');
        }

        $status = Get::groupContactStatus($contact_id, $group_id, $check_permissions);

        switch ($status) {
            case Get::GROUP_CONTACT_STATUS_NONE:
                Create::entity('GroupContact', [
                    'group_id' => $group_id,
                    'contact_id' => $contact_id,
                    'status' => 'Removed',
                ], $check_permissions);

                $affected_rows = 1;
                break;
            case Get::GROUP_CONTACT_STATUS_REMOVED:
                $affected_rows = 0;
                break;
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
                $group_contact_id = Get::entitySingle('GroupContact', $params, 'id', $check_permissions);
                if (is_null($group_contact_id)) {
                    // Status is added, but no record in group_contact table --> this is a smart group, add removed manually
                    Create::entity('GroupContact', [
                        'group_id' => $group_id,
                        'contact_id' => $contact_id,
                        'status' => 'Removed',
                    ], $check_permissions);
                } else {
                    // This is a normal group, update status
                    Update::entity('GroupContact', $group_contact_id, ['status' => 'Removed'], $check_permissions);
                }

                $affected_rows = 1;
                break;
            default:
                throw new APIException('GroupContact', 'get', "Invalid status returned: {$status}");
        }

        // Update cache manually if smart group cache is not expired yet --> so cache will be accurate even until regeneration
        if (Get::isSmartGroup($group_id, $check_permissions) && !CRM_Contact_BAO_GroupContactCache::shouldGroupBeRefreshed($group_id)) {
            // Check record present
            $sql = 'SELECT contact_id
                    FROM civicrm_group_contact_cache
                    WHERE contact_id = %1 AND group_id = %2
                    LIMIT 1';
            $group_contact_cache = DB::query($sql, [
                1 => [$contact_id, 'Positive'],
                2 => [$group_id, 'Positive'],
            ]);

            // Remove from cache
            if (!empty($group_contact_cache)) {
                $sql = 'DELETE FROM civicrm_group_contact_cache WHERE contact_id = %1 AND group_id = %2';
                DB::query($sql, [
                    1 => [$contact_id, 'Positive'],
                    2 => [$group_id, 'Positive'],
                ]);
            }
        }

        return $affected_rows;
    }

    /**
     * Remove all contacts from a group
     *
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Number of removed contacts
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function emptyGroup(int $group_id, bool $check_permissions = false): int
    {
        if ($group_id < 1) {
            throw new InvalidArgumentException('ID', 'must be positive');
        }

        try {
            $contacts = GroupContact::update($check_permissions)
                ->addValue('status', 'Removed')
                ->addWhere('group_id', '=', $group_id)
                ->addClause('OR', ['status', '=', 'Pending'], ['status', '=', 'Added'])
                ->execute();
        } catch (Throwable $ex) {
            throw new APIException('GroupContact', 'update', $ex->getMessage(), $ex);
        }

        return count($contacts);
    }

    /**
     * Remove tag from contact
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Number of affected contacts (1 if contact had tag before, 0 if hadn't)
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function tagFromContact(int $contact_id, int $tag_id, bool $check_permissions = false): int
    {
        // Check if still tagged
        $entity_tag_id = Get::contactHasTag($contact_id, $tag_id, $check_permissions);
        if (is_null($entity_tag_id)) {
            return 0;
        }

        self::entity('EntityTag', $entity_tag_id, $check_permissions);

        return 1;
    }
}
