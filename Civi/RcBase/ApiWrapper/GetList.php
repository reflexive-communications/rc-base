<?php

namespace Civi\RcBase\ApiWrapper;

/**
 * Common Get Actions, mainly for listing multiple records
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class GetList
{
    /**
     * Get all option values for an option group.
     * By default an array of ['value' => 'label'] pairs is returned.
     * With $index and $extra parameters you can very flexibly define the return value,
     * but results are always limited to the selected option group.
     *
     * @param string $option_group Which option group to get
     * @param array $extra Extra parameters for the APIv4 call
     * @param mixed $index Index of the return array
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function optionValues(string $option_group, array $extra = [], $index = ['value' => 'label'], bool $check_permissions = false): array
    {
        if (empty($option_group)) {
            return [];
        }

        $default = [
            'orderBy' => ['label' => 'ASC'],
        ];
        $params = array_merge($default, $extra);
        // Add option group filter after merge, so it can't be overwritten
        $params['where'][] = ['option_group_id:name', '=', $option_group];

        return Get::entity('OptionValue', $params, $index, $check_permissions)->getArrayCopy();
    }
}
