<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\Generic\Result;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Throwable;

/**
 * Common Get Actions
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Get
{
    /**
     * Record type id when contact is the assignee of the activity
     */
    public const ACTIVITY_RECORD_TYPE_ASSIGNEE = 1;

    /**
     * Record type id when contact is the source of the activity
     */
    public const ACTIVITY_RECORD_TYPE_SOURCE = 2;

    /**
     * Record type id when contact is the target of the activity
     */
    public const ACTIVITY_RECORD_TYPE_TARGET = 3;

    /**
     * Status represents contact was never in given group
     */
    public const GROUP_CONTACT_STATUS_NONE = 1;

    /**
     * Status represents contact is in given group
     */
    public const GROUP_CONTACT_STATUS_ADDED = 2;

    /**
     * Status represents contact was removed from given group
     */
    public const GROUP_CONTACT_STATUS_REMOVED = 3;

    /**
     * Status represents contact is pending in given group
     */
    public const GROUP_CONTACT_STATUS_PENDING = 4;

    /**
     * Retrieve entity
     *
     * @param string $entity Entity name
     * @param array $params Parameters for get query
     * @param mixed $index Controls the Result array format. For details see civicrm_api4
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return \Civi\Api4\Generic\Result
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function entity(string $entity, array $params = [], $index = null, bool $check_permissions = false): Result
    {
        $params['checkPermissions'] = $check_permissions;

        try {
            $results = civicrm_api4($entity, 'get', $params, $index);
        } catch (Throwable $ex) {
            throw new APIException($entity, 'get', $ex->getMessage(), $ex);
        }

        return $results;
    }

    /**
     * Parse result set, return first row
     *
     * @param Result $results Api4 Result set
     * @param string $field Field to return
     *
     * @return mixed|null
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function parseResultsFirst(Result $results, string $field = '')
    {
        if (count($results) < 1) {
            return null;
        }

        $result = $results->first();

        if (!empty($field)) {
            if (!array_key_exists($field, $result)) {
                throw new APIException($results->entity, $results->action, "{$field} not found");
            }

            return $result[$field];
        }

        // No field specified --> return all fields
        return $result;
    }

    /**
     * Retrieve record by ID
     *
     * @param string $entity Entity name
     * @param int $id Entity ID
     * @param string $field Optional field to filter (if empty return all fields)
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return mixed|null
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function entityByID(string $entity, int $id, string $field = '', bool $check_permissions = false)
    {
        // No field specified --> return all fields
        $select = empty($field) ? '*' : $field;

        $params = [
            'select' => [$select],
            'where' => [['id', '=', $id]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity($entity, $params, $check_permissions), $field);
    }

    /**
     * Retrieve record by machine-name
     *
     * @param string $entity Entity name
     * @param string $name Record name/programmatic handle
     * @param string $field Optional field to filter (if empty return all fields)
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return mixed|null
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function entityByName(string $entity, string $name, string $field = '', bool $check_permissions = false)
    {
        // No field specified --> return all fields
        $select = empty($field) ? '*' : $field;

        $params = [
            'select' => [$select],
            'where' => [['name', '=', $name]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity($entity, $params, $check_permissions), $field);
    }

    /**
     * Get contact ID from email
     *
     * @param string $email Email address
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contact ID if found, null if not found
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function contactIDByEmail(string $email, bool $check_permissions = false): ?int
    {
        if (empty($email)) {
            return null;
        }

        $params = [
            'select' => ['contact_id'],
            'where' => [['email', '=', $email]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('Email', $params, $check_permissions), 'contact_id');
    }

    /**
     * Get contact ID of system user
     *
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contact ID
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function systemUserContactID(bool $check_permissions = false): ?int
    {
        $params = [
            'select' => ['contact_id'],
            'where' => [['uf_id', '=', 1]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('UFMatch', $params, $check_permissions), 'contact_id');
    }

    /**
     * Get ID of default Location type
     *
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Location type ID if found, null if not found
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function defaultLocationTypeID(bool $check_permissions = false): ?int
    {
        $params = [
            'select' => ['id'],
            'where' => [['is_default', '=', true]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('LocationType', $params, $check_permissions), 'id');
    }

    /**
     * Check if tag is applied to a contact
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null EntityTag ID if found, null if not found
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function contactHasTag(int $contact_id, int $tag_id, bool $check_permissions = false): ?int
    {
        if ($contact_id < 1 || $tag_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $params = [
            'select' => ['id'],
            'where' => [
                ['entity_table', '=', 'civicrm_contact'],
                ['entity_id', '=', $contact_id],
                ['tag_id', '=', $tag_id],
            ],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('EntityTag', $params, $check_permissions), 'id');
    }

    /**
     * Get ID of the parent of a tag
     *
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Parent Tag ID if found, null if not found
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function parentTagId(int $tag_id, bool $check_permissions = false): ?int
    {
        if ($tag_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $params = [
            'select' => ['parent_id'],
            'where' => [['id', '=', $tag_id]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('Tag', $params, $check_permissions), 'parent_id');
    }

    /**
     * Get current sub-types of a contact
     *
     * @param int $contact_id Contact ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array List of sub-types
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function contactSubType(int $contact_id, bool $check_permissions = false): array
    {
        if ($contact_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $params = [
            'select' => ['contact_sub_type'],
            'where' => [['id', '=', $contact_id]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('Contact', $params, $check_permissions), 'contact_sub_type') ?? [];
    }

    /**
     * Get group membership status for a contact
     *
     * @param int $contact_id Contact ID
     * @param int $group_id Group ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int Status code
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function groupContactStatus(int $contact_id, int $group_id, bool $check_permissions = false): int
    {
        if ($contact_id < 1 || $group_id < 1) {
            throw new InvalidArgumentException('ID');
        }

        $params = [
            'select' => ['status'],
            'where' => [
                ['contact_id', '=', $contact_id],
                ['group_id', '=', $group_id],
            ],
            'limit' => 1,
        ];

        $status = self::parseResultsFirst(self::entity('GroupContact', $params, $check_permissions), 'status');

        switch ($status) {
            case 'Added':
                return self::GROUP_CONTACT_STATUS_ADDED;
            case 'Removed':
                return self::GROUP_CONTACT_STATUS_REMOVED;
            case 'Pending':
                return self::GROUP_CONTACT_STATUS_PENDING;
            case null:
                return self::GROUP_CONTACT_STATUS_NONE;
            default:
                throw new APIException('GroupContact', 'get', "Invalid status returned: {$status}");
        }
    }

    /**
     * Get value of an option
     *
     * @param string $option_group Name of option group
     * @param string $option_name Name of option
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return string|null Value of option
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function optionValue(string $option_group, string $option_name, bool $check_permissions = false): ?string
    {
        if (empty($option_group) || empty($option_name)) {
            return null;
        }

        $params = [
            'select' => ['value'],
            'where' => [
                ['option_group_id:name', '=', $option_group],
                ['name', '=', $option_name],
            ],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('OptionValue', $params, $check_permissions), 'value');
    }

    /**
     * Get contribution ID from transaction ID
     *
     * @param string $transaction_id Transaction ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contribution ID if found, null if not found
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function contributionIDByTransactionID(string $transaction_id, bool $check_permissions = false): ?int
    {
        if (empty($transaction_id)) {
            return null;
        }

        $params = [
            'select' => ['id'],
            'where' => [['trxn_id', '=', $transaction_id]],
            'limit' => 1,
        ];

        return self::parseResultsFirst(self::entity('Contribution', $params, $check_permissions), 'id');
    }
}
