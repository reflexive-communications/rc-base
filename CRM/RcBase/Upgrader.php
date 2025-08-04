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
     * @param string $routine_name
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    protected static function installProcedure(string $routine_name): void
    {
        $sql = CRM_Utils_File::stripComments(file_get_contents(E::path("sql/{$routine_name}.sql")));
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
        self::installProcedure('noop');
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
        self::dropProcedure('noop');
    }

    /**
     * Install SQL procedures.
     *
     * @return bool
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function upgrade_1620(): bool
    {
        self::installProcedure('noop');

        return true;
    }
}
