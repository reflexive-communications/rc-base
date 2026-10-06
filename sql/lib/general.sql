-- /******************************
-- *
-- * General SQL helper procedures
-- *
-- ******************************/

-- ---------------------------------------
-- Execute SQL query as prepared statement
-- ---------------------------------------
CREATE OR REPLACE PROCEDURE rc_execute(
    sql_query LONGTEXT,
    OUT affected_rows INT
)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT "Execute SQL query as prepared statement"
BEGIN
    IF sql_query IS NULL OR CHAR_LENGTH(TRIM(sql_query)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SQL statement must not be empty';
    END IF;

    PREPARE stmt FROM sql_query;
    EXECUTE stmt;
    SET affected_rows = ROW_COUNT();
    DEALLOCATE PREPARE stmt;
END;
