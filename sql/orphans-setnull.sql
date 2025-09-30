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
MODIFIES SQL DATA COMMENT "Find orphan records (that refer to a non-existing record) and set chield field NULL"
BEGIN
    CREATE TEMPORARY TABLE tmp_orphans (id BIGINT PRIMARY KEY);
    SET @sql = CONCAT(
        'INSERT INTO tmp_orphans (id) ',
        'SELECT DISTINCT orig.`', orig_field, '` ',
        'FROM `', orig_table, '` orig ',
        'LEFT JOIN `', ref_table, '` ref ON orig.`', orig_field, '` = ref.`', ref_field, '` ',
        'WHERE ref.`', ref_field, '` IS NULL AND orig.`', orig_field, '` IS NOT NULL');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @sql = CONCAT('UPDATE `', orig_table, '` SET `', orig_field, '` = NULL WHERE `', orig_field, '` IN (SELECT id FROM tmp_orphans)');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    SET affected_rows = ROW_COUNT();
    DEALLOCATE PREPARE stmt;

    DROP TEMPORARY TABLE IF EXISTS tmp_orphans;
END
