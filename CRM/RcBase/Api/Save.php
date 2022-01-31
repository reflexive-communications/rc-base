<?php

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
}
