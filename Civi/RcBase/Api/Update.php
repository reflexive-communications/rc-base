<?php

namespace Civi\RcBase\Api;

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\Result;

/**
 * Common Update Actions
 *
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Update
{
    /**
     * Check if update operation succeeded and return updated data
     *
     * @param Result $results API call results
     * @param string $action Operation name (for logging & reporting)
     *
     * @return array Data
     *
     * @throws CRM_Core_Exception
     */
    protected static function parseResults(Civi\Api4\Generic\Result $results, string $action): array
    {
        $data = $results->first();

        if (is_null($data) || !is_array($data)) {
            throw new CRM_Core_Exception(sprintf('Failed to %s', $action));
        }

        return $data;
    }

    /**
     * Update generic entity
     *
     * @param string $entity Name of entity
     * @param int $entity_id Entity ID
     * @param array $values Entity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated entity data
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function entity(
        string $entity,
        int $entity_id,
        array $values = [],
        bool $check_permissions = false
    ): array {
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

        return self::parseResults($results, sprintf('update %s (ID: %s)', $entity, $entity_id));
    }

    /**
     * Update contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Updated Contact data
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
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
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
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
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
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
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
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
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
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
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws NotImplementedException
     */
    public static function activity(int $activity_id, array $values = [], bool $check_permissions = false): array
    {
        return self::entity('Activity', $activity_id, $values, $check_permissions);
    }
}
