<?php

use Civi\Test;

/**
 * Contains helper functions for unit-testing
 *
 * @deprecated
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 * @package  rc-base
 */
class CRM_RcBase_Test_Utils
{
    /**
     * Executes a raw SQL query on the DB
     *
     * @param string $query SQL query
     *
     * @return array Query results indexed by column name
     * @throws \CRM_Core_Exception
     * @deprecated use \Civi\RcBase\Utils\DB::query()
     */
    public static function rawSqlQuery(string $query): array
    {
        if (empty($query)) {
            throw new CRM_Core_Exception('Missing SQL query');
        }

        $pdo = Test::pdo();

        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get next auto-increment value for an SQL table
     *
     * @param string $table_name Name of table
     *
     * @return int Next auto-increment value
     * @throws \CRM_Core_Exception
     * @deprecated Use \Civi\RcBase\Utils\DB::getNextAutoIncrementValue instead
     */
    public static function getNextAutoIncrementValue(string $table_name): int
    {
        if (empty($table_name)) {
            throw new CRM_Core_Exception('Missing table name');
        }

        $results = self::rawSqlQuery("SHOW TABLE STATUS WHERE name='{$table_name}'");

        if (count($results) < 1) {
            throw new CRM_Core_Exception("Table {$table_name} not found in DB");
        }

        return (int)$results[0]['Auto_increment'];
    }

    /**
     * Call cv api4 with get action
     *
     * @param string $entity Entity to work on (Contact, Email, etc.)
     * @param array $select Fields to return
     *   Example:
     *   $select = ['contact_type', 'first_name', 'external_identifier']
     * @param array $where Where conditions to filter results (if more given they are joined by AND)
     *   Example:
     *   $where = [
     *   'contact_type=Individual',
     *   'first_name like "Adams%",
     *   ]
     *
     * @return array Results
     * @throws \CRM_Core_Exception
     * @deprecated Use \Civi\RcBase\ApiWrapper\Get
     */
    public static function cvApi4Get(string $entity, array $select = [], array $where = []): array
    {
        if (empty($entity)) {
            throw new CRM_Core_Exception('Missing entity name');
        }

        // Parse parameters and assemble command
        $select_string = '';
        if (!empty($select)) {
            $select_string = implode(',', $select);
            $select_string = "+s '{$select_string}'";
        }

        $where_string = '';
        foreach ($where as $item) {
            $where_string .= "+w '{$item}' ";
        }

        $command = "api4 {$entity}.get {$select_string} {$where_string}";

        // Run command
        $result = cv($command);

        // Check results
        if (!is_array($result)) {
            throw new CRM_Core_Exception("Not an array returned from '{$command}'");
        }

        // Check each record
        foreach ($result as $record) {
            if (!is_array($result)) {
                throw new CRM_Core_Exception("Not an array returned from '{$command}'");
            }

            // Check if selected fields are present
            foreach ($select as $item) {
                if (!array_key_exists($item, $record)) {
                    throw new CRM_Core_Exception("{$item} not returned");
                }
            }
        }

        return $result;
    }

    /**
     * Call cv api4 with create action
     *
     * @param string $entity Entity to work on (Contact, Email, etc.)
     * @param array $params Params of entity
     *
     * @return int Created entity ID
     * @throws \CRM_Core_Exception
     * @deprecated Use \Civi\RcBase\ApiWrapper\Create
     */
    public static function cvApi4Create(string $entity, array $params = []): int
    {
        if (empty($entity)) {
            throw new CRM_Core_Exception('Missing entity name');
        }

        // Parse parameters and assemble command
        $values = [
            'values' => $params,
        ];
        $values_json = json_encode($values);

        $command = "api4 {$entity}.create '{$values_json}'";

        // Run command
        $result = cv($command);

        // Check results
        if (!is_array($result)) {
            throw new CRM_Core_Exception("Not an array returned from '{$command}'");
        }
        if (count($result) != 1) {
            throw new CRM_Core_Exception("Not one result returned from '{$command}'");
        }
        if (!is_array($result[0])) {
            throw new CRM_Core_Exception("Not an array returned from '{$command}'");
        }
        if (!array_key_exists('id', $result[0])) {
            throw new CRM_Core_Exception('ID not found.');
        }

        return (int)$result[0]['id'];
    }
}
