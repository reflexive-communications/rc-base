<?php

/**
 * CRM_RcBase_Permissions Class
 */
class CRM_RcBase_Permissions
{
    /**
     * Custom permissions
     *
     * format:
     *   'permission_name' => ['label', 'description']
     */
    public const PERMISSIONS
        = [
            'access custom API' => ['CiviCRM: access custom API', 'Allow access to custom API endpoints'],
        ];

    /**
     * Add custom permissions
     *
     * @param $permissions
     */
    public static function addCustomPermissions(&$permissions)
    {
        foreach (self::PERMISSIONS as $permission_name => $details) {
            $permissions[$permission_name] = $details;
        }
    }
}