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
}
