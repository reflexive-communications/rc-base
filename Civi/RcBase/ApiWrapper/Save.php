<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Utils\DB;
use CRM_Contact_BAO_GroupContactCache;

/**
 * Common Save Actions
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Save
{
    /**
     * Add tag to contact
     * Check if contact is already tagged before tagging
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Entity tag ID
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function tagContact(int $contact_id, int $tag_id, bool $check_permissions = false): ?int
    {
        // Check if already tagged
        $entity_tag_id = Get::contactHasTag($contact_id, $tag_id, $check_permissions);
        if (!is_null($entity_tag_id)) {
            return $entity_tag_id;
        }

        return Create::tagContact($contact_id, $tag_id, $check_permissions);
    }

    /**
     * Add extra sub-type to a contact, current sub-types are preserved
     *
     * @param int $contact_id Contact ID
     * @param array $subtypes List of sub-types to add
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Contact data
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function addSubTypeToContact(int $contact_id, array $subtypes, bool $check_permissions = false): array
    {
        $current_sub_types = Get::contactSubType($contact_id, $check_permissions);

        return Update::contact($contact_id, ['contact_sub_type' => array_merge($current_sub_types, $subtypes)], $check_permissions);
    }

    /**
     * Add contact to group
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     * @param bool $smart_group Update group_contact_cache also
     *
     * @return int Group contact ID
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @todo Change signature in v2
     */
    public static function addContactToGroup(int $contact_id, int $group_id, bool $check_permissions = false, bool $smart_group = false): int
    {
        $status = Get::groupContactStatus($contact_id, $group_id, $check_permissions);

        switch ($status) {
            case Get::GROUP_CONTACT_STATUS_NONE:
                $values = [
                    'group_id' => $group_id,
                    'contact_id' => $contact_id,
                    'status' => 'Added',
                ];
                $group_contact_id = Create::entity('GroupContact', $values, $check_permissions);
                break;
            case Get::GROUP_CONTACT_STATUS_REMOVED:
            case Get::GROUP_CONTACT_STATUS_PENDING:
                $params = [
                    'select' => ['id'],
                    'where' => [
                        ['contact_id', '=', $contact_id],
                        ['group_id', '=', $group_id],
                    ],
                    'limit' => 1,
                ];
                $group_contact_id = Get::entitySingle('GroupContact', $params, 'id', $check_permissions);
                Update::entity('GroupContact', $group_contact_id, ['status' => 'Added'], $check_permissions);
                break;
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
                break;
            default:
                throw new APIException('GroupContact', 'get', "Invalid status returned: {$status}");
        }

        // Update cache manually if cache is not expired yet --> so cache will be accurate even until regeneration
        if ($smart_group && CRM_Contact_BAO_GroupContactCache::check([$group_id])) {
            // Check record present
            $sql = 'SELECT contact_id
                    FROM civicrm_group_contact_cache
                    WHERE contact_id = %1 AND group_id = %2
                    LIMIT 1';
            $group_contact_cache = DB::query($sql, [
                1 => [$contact_id, 'Positive'],
                2 => [$group_id, 'Positive'],
            ]);

            // Add to cache
            if (empty($group_contact_cache)) {
                $sql = 'INSERT INTO civicrm_group_contact_cache (id, group_id, contact_id) VALUES (NULL, %2, %1)';
                DB::query($sql, [
                    1 => [$contact_id, 'Positive'],
                    2 => [$group_id, 'Positive'],
                ]);
            }
        }

        return $group_contact_id;
    }
}
