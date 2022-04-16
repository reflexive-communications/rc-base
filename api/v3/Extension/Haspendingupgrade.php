<?php

/**
 * Extension.haspendingupgrade API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 */
function civicrm_api3_extension_haspendingupgrade(array $params): array
{
    try {
        $return = CRM_Extension_Upgrades::hasPending() ? 1 : 0;
        return civicrm_api3_create_success($return, $params, 'Extension', 'haspendingupgrade');
    } catch (Throwable $ex) {
        return civicrm_api3_create_error($ex->getMessage());
    }
}
