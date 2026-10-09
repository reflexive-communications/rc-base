-- /*******************************************************
-- *
-- * Delete orphan records
-- * (that refer to a non-existing record) from a table.
-- *
-- *******************************************************/
CREATE OR REPLACE PROCEDURE civicrm_delete_orphans(
    orig_table VARCHAR(128),
    orig_field VARCHAR(128),
    ref_table VARCHAR(128),
    ref_field VARCHAR(128),
    OUT affected_rows INT
)
    MODIFIES SQL DATA COMMENT "Delete orphan records (that refer to a non-existing record) from a table"
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
            'LEFT JOIN', ref_table, 'ref ON', CONCAT('orig.', orig_field, '= ref.', ref_field),
            'WHERE', CONCAT('ref.', ref_field), 'IS NULL AND', CONCAT('orig.', orig_field), 'IS NOT NULL'),
        @ignored);

    CALL execute_sql(
        CONCAT_WS(' ', 'DELETE FROM', orig_table, 'WHERE', orig_field, 'IN (SELECT id FROM tmp_orphans)'), affected_rows);

    DROP TEMPORARY TABLE IF EXISTS tmp_orphans;
END
