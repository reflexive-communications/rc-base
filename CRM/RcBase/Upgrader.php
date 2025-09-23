<?php

use CRM_RcBase_ExtensionUtil as E;

/**
 * Installer
 */
class CRM_RcBase_Upgrader extends CRM_Extension_Upgrader_Base
{
    /**
     * Install a SQL procedure.
     *
     * @param string $routine_file SQL file containing the procedure definition.
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    protected static function installProcedure(string $routine_file): void
    {
        $sql = CRM_Utils_File::stripComments(file_get_contents($routine_file));
        \Civi\RcBase\Utils\DB::query($sql);
    }

    /**
     * @param string $routine_name
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    protected static function dropProcedure(string $routine_name): void
    {
        \Civi\RcBase\Utils\DB::query("DROP PROCEDURE IF EXISTS {$routine_name}");
    }

    /**
     * Install steps:
     *   - install SQL procedures
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function install(): void
    {
        self::installProcedure(E::path('sql/delete-orphans.sql'));
    }

    /**
     * Uninstall:
     *   - remove SQL procedures
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function uninstall(): void
    {
        self::dropProcedure('civicrm_delete_orphans');
    }

    /**
     * Install SQL procedures.
     *
     * @return bool
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function upgrade_1620(): bool
    {
        self::installProcedure(E::path('sql/delete-orphans.sql'));

        return true;
    }
}
