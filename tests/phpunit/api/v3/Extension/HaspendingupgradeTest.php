<?php

/**
 * Extension.Haspendingupgrade API Test Case
 *
 * @group headless
 */
class api_v3_Extension_HaspendingupgradeTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @throws \CiviCRM_API3_Exception
     */
    public function testHaspendingupgradeWithNoPendingUpgrades()
    {
        $result = civicrm_api3('Extension', 'haspendingupgrade');
        self::assertArrayHasKey('is_error', $result, 'is_error key missing');
        self::assertArrayHasKey('values', $result, 'values key missing');
        self::assertEquals(0, $result['is_error'], 'Error returned');
        self::assertEquals(0, $result['values'], 'Pending DB upgrades reported');
    }
}
