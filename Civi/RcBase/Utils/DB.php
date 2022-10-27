<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\DataBaseException;
use Civi\RcBase\Exception\MissingArgumentException;
use CRM_Core_DAO;
use Throwable;

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
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function getNextAutoIncrementValue(string $table_name): int
    {
        if (empty($table_name)) {
            throw new MissingArgumentException('table name');
        }

        $result = self::query('SHOW TABLE STATUS WHERE name=%1', [1 => [$table_name, 'String']]);
        $auto_increment = $result[0]['Auto_increment'] ?? 0;

        if ($auto_increment === 0) {
            throw new DataBaseException("Failed to get next auto increment value for table: {$table_name}");
        }

        return $auto_increment;
    }

    /**
     * Execute SQL query, wrapper for CRM_Core_DAO::executeQuery
     *
     * @param string $sql SQL statement with optionally placeholders
     *
     *   'SELECT * FROM civicrm_contact WHERE id = %1'
     *
     * @param array $params Params to insert to placeholders
     *
     *   $params = [
     *     1 => [$id, 'Integer']
     *   ]
     *
     * @return array
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public static function query(string $sql, array $params = []): array
    {
        try {
            return CRM_Core_DAO::executeQuery($sql, $params)->fetchAll();
        } catch (Throwable $ex) {
            throw new DataBaseException($ex->getMessage());
        }
    }
}
