<?php

use Civi\RcBase\HeadlessTestCase;
use CRM_RcBase_ExtensionUtil as E;

/**
 * @group headless
 */
class CRM_RcBase_UpgraderTest extends HeadlessTestCase
{
    /**
     * Get routine by name from information_schema.ROUTINES.
     *
     * @param string $routine_name
     *
     * @return array
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    protected static function getRoutine(string $routine_name): array
    {
        return \Civi\RcBase\Utils\DB::query(
            'SELECT * FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = "PROCEDURE" AND ROUTINE_NAME = %1',
            [1 => [$routine_name, 'String']]
        );
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testInstall()
    {
        $installer = new CRM_RcBase_Upgrader();
        $installer->install();

        self::assertCount(1, self::getRoutine('noop'), 'SQL procedure "noop" not found');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testUninstall()
    {
        $installer = new CRM_RcBase_Upgrader();
        $installer->uninstall();

        self::assertEmpty(self::getRoutine('noop'), 'SQL procedure "noop" not removed');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testUpgrade1620()
    {
        // Simulate state before update
        $installer = new CRM_RcBase_Upgrader();
        $installer->executeSqlFile(E::path('sql/uninstall.sql'));

        self::assertTrue($installer->upgrade_1620(), 'Upgrade failed');

        self::assertCount(1, self::getRoutine('noop'), 'SQL procedure "noop" not found');

        // Run upgrade again --> now nothing to do
        $installer->upgrade_1620();
        self::assertTrue($installer->upgrade_1620(), 'No-op upgrade failed');
        self::assertCount(1, self::getRoutine('noop'), 'SQL procedure "noop" not found');
    }
}
