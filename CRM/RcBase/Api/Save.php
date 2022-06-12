<?php

use Civi\Api4\GroupContact;

/**
 * Common Save Actions
 *
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Api_Save
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
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function tagContact(int $contact_id, int $tag_id, bool $check_permissions = false): ?int
    {
        $entity_tag_id = CRM_RcBase_Api_Get::contactHasTag($contact_id, $tag_id, $check_permissions);

        // Already has tag
        if ($entity_tag_id) {
            return null;
        }

        // No tag present --> tag contact
        return CRM_RcBase_Api_Create::tagContact($contact_id, $tag_id, $check_permissions);
    }

    /**
     * Add extra sub-type to a contact, current sub-types are preserved
     *
     * @param int $contact_id Contact ID
     * @param array $subtypes List of sub-types to add
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Contact data
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function addSubTypeToContact(int $contact_id, array $subtypes, bool $check_permissions = false): array
    {
        $current_sub_types = CRM_RcBase_Api_Get::contactSubType($contact_id, $check_permissions);
        return CRM_RcBase_Api_Update::contact($contact_id, ['contact_sub_type' => array_merge($current_sub_types, $subtypes),], $check_permissions);
    }

    /**
     * Add contact to group
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Group contact ID
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function addContactToGroup(int $contact_id, int $group_id, bool $check_permissions = false): int
    {
        $status = CRM_RcBase_Api_Get::groupContactStatus($contact_id, $group_id, $check_permissions);

        switch ($status) {
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_NONE:
                $values = [
                    'group_id' => $group_id,
                    'contact_id' => $contact_id,
                    'status' => 'Added',
                ];
                $group_contact_id = CRM_RcBase_Api_Create::entity('GroupContact', $values, $check_permissions);
                break;
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_REMOVED:
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_PENDING:
                $result = GroupContact::get($check_permissions)
                    ->addSelect('id')
                    ->addWhere('group_id', '=', $group_id)
                    ->addWhere('contact_id', '=', $contact_id)
                    ->setLimit(1)
                    ->execute();
                $group_contact_id = CRM_RcBase_Api_Get::parseResultsFirst($result, 'id');
                CRM_RcBase_Api_Update::entity('GroupContact', $group_contact_id, ['status' => 'Added',], $check_permissions);
                break;
            case CRM_RcBase_Api_Get::GROUP_CONTACT_STATUS_ADDED:
                $result = GroupContact::get($check_permissions)
                    ->addSelect('id')
                    ->addWhere('group_id', '=', $group_id)
                    ->addWhere('contact_id', '=', $contact_id)
                    ->setLimit(1)
                    ->execute();
                $group_contact_id = CRM_RcBase_Api_Get::parseResultsFirst($result, 'id');
                break;
            default:
                throw new API_Exception(sprintf('Invalid status returned: %s', $status));
        }

        return $group_contact_id;
    }
}
