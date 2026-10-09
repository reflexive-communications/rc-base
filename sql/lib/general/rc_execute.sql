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
    CALL rc_validate_not_empty('sql_query', sql_query);

    PREPARE stmt FROM sql_query;
    EXECUTE stmt;
    SET affected_rows = ROW_COUNT();
    DEALLOCATE PREPARE stmt;
END;
