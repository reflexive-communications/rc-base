<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\Generic\Result;
use Civi\RcBase\Exception\APIException;
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
}
