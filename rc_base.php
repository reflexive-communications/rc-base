<?php

require_once 'rc_base.civix.php';

// phpcs:disable
use CRM_RcBase_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function rc_base_civicrm_config(&$config)
{
    _rc_base_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_permission().
 */
function rc_base_civicrm_permission(&$permissions)
{
    CRM_RcBase_Permissions::addCustomPermissions($permissions);
}
