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
}
