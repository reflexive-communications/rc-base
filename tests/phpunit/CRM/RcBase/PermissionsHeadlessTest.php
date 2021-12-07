<?php

/**
 * Test Permissions class
 *
 * @group headless
 */
class CRM_RcBase_PermissionsHeadlessTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * Test custom permissions are available
     */
    public function testPermissions()
    {
        $permissions = CRM_Core_Permission::basicPermissions();

        foreach (CRM_RcBase_Permissions::PERMISSIONS as $permission_name => $details) {
            self::assertArrayHasKey($permission_name, $permissions, "Not found permission: ${permission_name}");
            self::assertSame($details[0], $permissions[$permission_name], "Wrong label for: ${permission_name}");
        }
    }
}
