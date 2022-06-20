<?php

use Civi\Api4\OptionValue;

/**
 * Common Create Actions
 *
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Api_Create
{
    /**
     * Add new generic entity
     *
     * @param string $entity Name of entity
     * @param array $values Entity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int ID of created entity
     *
     * @throws CRM_Core_Exception
     */
    public static function entity(string $entity, array $values = [], bool $check_permissions = false): int
    {
        try {
            $results = civicrm_api4(
                $entity,
                'create',
                [
                    'values' => $values,
                    'checkPermissions' => $check_permissions,
                ]
            );
        } catch (Throwable $ex) {
            throw new CRM_Core_Exception(sprintf('Failed to create %s, reason: %s', $entity, $ex->getMessage()));
        }

        // No exception --> create was successful and we have an ID
        return (int)$results->first()['id'];
    }

    /**
     * Create new contact
     *
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Contact ID
     *
     * @throws CRM_Core_Exception
     */
    public static function contact(array $values = [], bool $check_permissions = false): int
    {
        return self::entity('Contact', $values, $check_permissions);
    }

    /**
     * Add email to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Email data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Email ID
     *
     * @throws CRM_Core_Exception
     */
    public static function email(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['contact_id'] = $contact_id;

        return self::entity('Email', $values, $check_permissions);
    }

    /**
     * Add phone to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Phone data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Phone ID
     *
     * @throws CRM_Core_Exception
     */
    public static function phone(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['contact_id'] = $contact_id;

        return self::entity('Phone', $values, $check_permissions);
    }

    /**
     * Add address to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Address data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Address ID
     *
     * @throws CRM_Core_Exception
     */
    public static function address(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['contact_id'] = $contact_id;

        return self::entity('Address', $values, $check_permissions);
    }

    /**
     * Add relationship to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Relationship data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Relationship ID
     *
     * @throws CRM_Core_Exception
     */
    public static function relationship(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['contact_id_a'] = $contact_id;

        return self::entity('Relationship', $values, $check_permissions);
    }

    /**
     * Add contribution to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Contribution data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Contribution ID
     *
     * @throws CRM_Core_Exception
     */
    public static function contribution(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['contact_id'] = $contact_id;

        return self::entity('Contribution', $values, $check_permissions);
    }

    /**
     * Add activity to contact
     *
     * @param int $contact_id Contact ID
     * @param array $values Activity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Activity ID
     *
     * @throws CRM_Core_Exception
     */
    public static function activity(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values['target_contact_id'] = $contact_id;

        return self::entity('Activity', $values, $check_permissions);
    }

    /**
     * Add tag to contact
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int EntityTag ID
     *
     * @throws CRM_Core_Exception
     */
    public static function tagContact(int $contact_id, int $tag_id, bool $check_permissions = false): int
    {
        if ($contact_id < 1 || $tag_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $values = [
            'entity_table' => 'civicrm_contact',
            'entity_id' => $contact_id,
            'tag_id' => $tag_id,
        ];

        return self::entity('EntityTag', $values, $check_permissions);
    }

    /**
     * Create group
     *
     * @param array $values Group data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Group ID
     *
     * @throws CRM_Core_Exception
     */
    public static function group(array $values = [], bool $check_permissions = false): int
    {
        return self::entity('Group', $values, $check_permissions);
    }

    /**
     * Create tag
     *
     * @param array $values Tag data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Tag ID
     *
     * @throws CRM_Core_Exception
     */
    public static function tag(array $values = [], bool $check_permissions = false): int
    {
        return self::entity('Tag', $values, $check_permissions);
    }

    /**
     * Add option
     *
     * @param string $option_group Name of option group
     * @param array $values Option data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return string|null  Value of option
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function optionValue(string $option_group, array $values = [], bool $check_permissions = false): ?string
    {
        $values['option_group_id.name'] = $option_group;
        $option_value_id = self::entity('OptionValue', $values, $check_permissions);
        $result = OptionValue::get($check_permissions)
            ->addSelect('value')
            ->addWhere('id', '=', $option_value_id)
            ->execute();

        return CRM_RcBase_Api_Get::parseResultsFirst($result, 'value');
    }
}
