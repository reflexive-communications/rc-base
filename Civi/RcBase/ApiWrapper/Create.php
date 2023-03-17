<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Throwable;

/**
 * Common Create Actions
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Create
{
    /**
     * Add new entity
     *
     * @param string $entity Name of entity
     * @param array $values Entity data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int ID of created entity
     * @throws \Civi\RcBase\Exception\APIException
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
            throw new APIException($entity, 'create', $ex->getMessage(), $ex);
        }

        if (count($results) < 1) {
            throw new APIException($entity, 'create', 'Failed to create entity');
        }

        $id = ($results->first()['id']) ?? 0;
        if ($id < 1) {
            throw new APIException($entity, 'create', 'Not a valid ID returned');
        }

        return (int)$id;
    }

    /**
     * Create new contact
     *
     * @param array $values Contact data
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Contact ID
     * @throws \Civi\RcBase\Exception\APIException
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function email(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function phone(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function address(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function relationship(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function contribution(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function activity(int $contact_id, array $values = [], bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function tagContact(int $contact_id, int $tag_id, bool $check_permissions = false): int
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('contact ID', 'ID must be positive');
        }
        if ($tag_id < 1) {
            throw new InvalidArgumentException('tag ID', 'ID must be positive');
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
     * @throws \Civi\RcBase\Exception\APIException
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
     * @throws \Civi\RcBase\Exception\APIException
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
     * @return string|null Value of option
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function optionValue(string $option_group, array $values = [], bool $check_permissions = false): ?string
    {
        if (empty($option_group)) {
            throw new MissingArgumentException('option group name');
        }

        $values['option_group_id.name'] = $option_group;
        $option_value_id = self::entity('OptionValue', $values, $check_permissions);

        return Get::entityByID('OptionValue', $option_value_id, 'value');
    }
}
