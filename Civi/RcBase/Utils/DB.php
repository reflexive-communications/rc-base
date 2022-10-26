<?php

namespace Civi\RcBase\Utils;

use CRM_Core_DAO;
use CRM_Core_Exception;

/**
 * Utilities for DataBase
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class DB
{
    /**
     * MySQL timestamp format
     */
    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    /**
     * Minimum value for a MySQL timestamp field
     */
    public const TIMESTAMP_MIN_VALUE = '1970-01-01 00:00:01';

    /**
     * Get next auto increment value for a table (effectively next id)
     *
     * @param string $table_name Table name
     *
     * @return int Auto increment value
     * @throws \CRM_Core_Exception
     */
    public static function getNextAutoIncrementValue(string $table_name): int
    {
        if (empty($table_name)) {
            throw new CRM_Core_Exception('Missing table name');
        }

        $dao = new CRM_Core_DAO();
        $sql = CRM_Core_DAO::composeQuery('SHOW TABLE STATUS WHERE name=%1', [1 => [$table_name, 'String']]);
        $dao->query($sql);

        $row = $dao->getDatabaseResult()->fetchRow(DB_FETCHMODE_ASSOC);
        $auto_increment = $row['Auto_increment'] ?? 0;

        if ($auto_increment === 0) {
            throw new CRM_Core_Exception(sprintf('Failed to get next auto increment value for table: %s', $table_name));
        }

        return $auto_increment;
    }
}
