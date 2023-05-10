<?php

use CRM_RcBase_ExtensionUtil as E;

/**
 * Installer
 */
class CRM_RcBase_Upgrader extends CRM_Extension_Upgrader_Base
{
    /**
     * Install steps:
     *   - install SQL functions
     *
     * @return void
     */
    public function install(): void
    {
        $this->executeSqlFile(E::path('sql/functions.sql'));
    }

    /**
     * Uninstall:
     *   - remove SQL functions
     *
     * @return void
     */
    public function uninstall(): void
    {
    }

    public function upgrade_1380(): bool
    {
        $this->executeSqlFile(E::path('sql/functions.sql'));

        return true;
    }
}
