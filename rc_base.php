<?php

require_once 'rc_base.civix.php';

use Civi\RcBase\ContactDefaults;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function rc_base_civicrm_config(&$config): void
{
    _rc_base_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_pre().
 *
 * @param string $op
 * @param string $objectName
 * @param int|null $id
 * @param array $params
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre/
 */
function rc_base_civicrm_pre(string $op, string $objectName, $id, array &$params): void
{
    ContactDefaults::applyNoBulkDefault($op, $objectName, $params);
}
