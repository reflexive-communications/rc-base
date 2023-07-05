<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\DataBaseException;
use Civi\RcBase\Exception\MissingArgumentException;
use CRM_Core_DAO;
use CRM_Utils_Type;
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

        $result = self::query('SHOW TABLE STATUS WHERE name = %1', [1 => [$table_name, 'String']]);
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
     *   'SELECT * FROM civicrm_contact WHERE id = %1'
     * @param array $params Params to insert to placeholders
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
            throw new DataBaseException($ex->getMessage(), $ex);
        }
    }

    /**
     * Add contact to group, check current status first so only add if not present
     *
     * @param int $contact_id
     * @param int $group_id
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public static function addContactToGroup(int $contact_id, int $group_id): void
    {
        $sql = 'SELECT id, status
                FROM civicrm_group_contact
                WHERE contact_id = %1 AND group_id = %2
                LIMIT 1';
        $params = [
            1 => [$contact_id, 'Positive'],
            2 => [$group_id, 'Positive'],
        ];
        $record = self::query($sql, $params);

        // Contact never been in group --> add
        if (count($record) < 1) {
            $sql = 'INSERT INTO civicrm_group_contact (contact_id, group_id, status) VALUES (%1, %2, "Added")';
            self::query($sql, $params);

            return;
        }

        // Contact already in group
        if (($record[0]['status'] ?? '') == 'Added') {
            return;
        }

        $sql = 'UPDATE civicrm_group_contact SET status = "Added" WHERE id = %1';
        self::query($sql, [1 => [$record[0]['id'], 'Positive']]);
    }

    /**
     * Remove contact from group, check current status first so only remove if present
     *
     * @param int $contact_id
     * @param int $group_id
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public static function removeContactFromGroup(int $contact_id, int $group_id): void
    {
        $sql = 'SELECT id, status
                FROM civicrm_group_contact
                WHERE contact_id = %1 AND group_id = %2
                LIMIT 1';
        $params = [
            1 => [$contact_id, 'Positive'],
            2 => [$group_id, 'Positive'],
        ];
        $record = self::query($sql, $params);

        // Contact never been in group or removed already --> job done
        if (count($record) < 1 || ($record[0]['status'] ?? '') == 'Removed') {
            return;
        }

        $sql = 'UPDATE civicrm_group_contact SET status = "Removed" WHERE id = %1';
        self::query($sql, [1 => [$record[0]['id'], 'Positive']]);
    }

    /**
     * Normalize results set of DAO object:
     *   - cast strings to other scalar types (int, float, bool)
     *   - un-serialize values to arrays
     *
     * @param \CRM_Core_DAO $dao
     *
     * @return array
     * @throws \CRM_Core_Exception
     */
    public static function normalizeValues(CRM_Core_DAO $dao): array
    {
        $results = [];
        $fields_meta = $dao::fields();

        foreach ($dao->fetchAll() as $record) {
            if (empty($record)) {
                continue;
            }

            foreach ($record as $field => $value) {
                switch ($fields_meta[$field]['type'] ?? 0) {
                    case CRM_Utils_Type::T_INT:
                        $record[$field] = (int)$value;
                        break;
                    case CRM_Utils_Type::T_FLOAT:
                        $record[$field] = (float)$value;
                        break;
                    case CRM_Utils_Type::T_BOOLEAN:
                        $record[$field] = (bool)$value;
                        break;
                    default:
                        break;
                }

                if (isset($fields_meta[$field]['serialize'])) {
                    $record[$field] = $dao::unSerializeField($value, $fields_meta[$field]['serialize']);
                }
            }
            $results[] = $record;
        }

        return $results;
    }
}
