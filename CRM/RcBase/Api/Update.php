<?php

/**
 * Common Update Actions
 * Wrapper around APIv4
 *
 * @deprecated use \Civi\RcBase\ApiWrapper\Update instead
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Api_Update
{
    /**
     * Update generic entity
     *
     * @param string $entity Name of entity
     * @param int $entity_id Entity ID
     * @param array $values Entity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated entity data
     * @throws CRM_Core_Exception
     */
    public static function entity(
        string $entity,
        int $entity_id,
        array $values = [],
        bool $check_permissions = false
    ): array {
        if ($entity_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        try {
            $results = civicrm_api4(
                $entity,
                'update',
                [
                    'where' => [
                        ['id', '=', $entity_id],
                    ],
                    'values' => $values,
                    'limit' => 1,
                    'checkPermissions' => $check_permissions,
                ]
            );
        } catch (Throwable $ex) {
            throw new CRM_Core_Exception(sprintf('Failed to update %s, reason: %s', $entity, $ex->getMessage()));
        }

        return $results->first();
    }

    /**
     * Update contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Contact data
     * @throws CRM_Core_Exception
     */
    public static function contact(int $contact_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Contact', $contact_id, $values, $check_permissions);
    }

    /**
     * Update email
     *
     * @param int $email_id Email ID
     * @param array $values Email data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Email data
     * @throws CRM_Core_Exception
     */
    public static function email(int $email_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Email', $email_id, $values, $check_permissions);
    }

    /**
     * Update phone
     *
     * @param int $phone_id Phone ID
     * @param array $values Phone data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Phone data
     * @throws CRM_Core_Exception
     */
    public static function phone(int $phone_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Phone', $phone_id, $values, $check_permissions);
    }

    /**
     * Update address
     *
     * @param int $address_id Address ID
     * @param array $values Address data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Address data
     * @throws CRM_Core_Exception
     */
    public static function address(int $address_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Address', $address_id, $values, $check_permissions);
    }

    /**
     * Update relationship
     *
     * @param int $relationship_id Relationship ID
     * @param array $values Relationship data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Relationship data
     * @throws CRM_Core_Exception
     */
    public static function relationship(
        int $relationship_id,
        array $values = [],
        bool $check_permissions = false
    ): array {
        return self::entity('Relationship', $relationship_id, $values, $check_permissions);
    }

    /**
     * Update activity
     *
     * @param int $activity_id Activity ID
     * @param array $values Activity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Activity data
     * @throws CRM_Core_Exception
     */
    public static function activity(int $activity_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Activity', $activity_id, $values, $check_permissions);
    }

    /**
     * Update group
     *
     * @param int $group_id Group ID
     * @param array $values Group data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Group data
     * @throws \CRM_Core_Exception
     */
    public static function group(int $group_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Group', $group_id, $values, $check_permissions);
    }

    /**
     * Update tag
     *
     * @param int $tag_id Tag ID
     * @param array $values Tag data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Tag data
     * @throws \CRM_Core_Exception
     */
    public static function tag(int $tag_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Tag', $tag_id, $values, $check_permissions);
    }
}
