<?php

require_once 'rc_base.civix.php';

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
 * Implements hook_civicrm_permission().
 */
function rc_base_civicrm_permission(&$permissions): void
{
    CRM_RcBase_Permissions::addCustomPermissions($permissions);
}
