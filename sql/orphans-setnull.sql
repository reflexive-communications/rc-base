-- /**********************************************************
-- *
-- * Find orphan records (that refer to a non-existing record)
-- * and set child field NULL.
-- *
-- **********************************************************/
CREATE OR REPLACE PROCEDURE civicrm_setnull_orphans(
    orig_table VARCHAR(128),
    orig_field VARCHAR(128),
    ref_table VARCHAR(128),
    ref_field VARCHAR(128),
    OUT affected_rows INT
)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT "Find orphan records (that refer to a non-existing record) and set child field NULL"
BEGIN
    CREATE TEMPORARY TABLE tmp_orphans
    (
        id BIGINT PRIMARY KEY
    );
    CALL execute_sql(
        CONCAT_WS(
            ' ',
            'INSERT INTO tmp_orphans (id)',
            'SELECT DISTINCT', CONCAT('orig.', orig_field),
            'FROM', orig_table, 'orig',
            'LEFT JOIN', ref_table, 'ref ON', CONCAT('orig.', orig_field), '=', CONCAT('ref.', ref_field),
            'WHERE', CONCAT('ref.', ref_field), 'IS NULL AND', CONCAT('orig.', orig_field), 'IS NOT NULL'),
        @ignored);

    CALL execute_sql(
        CONCAT_WS(
            ' ',
            'UPDATE', orig_table,
            'SET', orig_field, '= NULL WHERE', CONCAT('orig.', orig_field), 'IN (SELECT id FROM tmp_orphans)'),
        affected_rows);

    DROP TEMPORARY TABLE IF EXISTS tmp_orphans;
END
