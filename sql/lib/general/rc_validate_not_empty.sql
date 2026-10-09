-- -----------------------------------
-- Validate that a string is not empty
-- -----------------------------------
CREATE OR REPLACE PROCEDURE rc_validate_not_empty(
    input_name VARCHAR(255),
    input_string LONGTEXT
)
    READS SQL DATA
    SQL SECURITY INVOKER
    DETERMINISTIC
    COMMENT "Validate that a string is not empty"
BEGIN
    IF input_string IS NULL OR CHAR_LENGTH(TRIM(input_string)) = 0 THEN
        SET @error_message = CONCAT(input_name, ': must not be empty');
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = @error_message;
    END IF;
END;
