-- ----------------------------------------------------------------------
-- Validate that a string is an identifier (e.g. table name, column name)
-- ----------------------------------------------------------------------
CREATE OR REPLACE PROCEDURE rc_validate_identifier(
    input_name VARCHAR(255),
    input_string LONGTEXT
)
    READS SQL DATA
    SQL SECURITY INVOKER
    DETERMINISTIC
    COMMENT "Validate that a string is a identifier (e.g. table name, column name)"
BEGIN
    CALL rc_validate_not_empty(input_name, input_string);
    IF input_string NOT REGEXP '^[A-Za-z_][A-Za-z0-9_$]*$' THEN
        SET @error_message = CONCAT(input_name, ': must be a valid identifier');
        SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = @error_message;
    END IF;
END;
