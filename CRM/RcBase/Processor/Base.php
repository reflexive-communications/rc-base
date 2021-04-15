<?php

/**
 * Base IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Processor_Base
{
    /**
     * Detect content-type
     *
     * @return string Relevant Processor class name
     */
    public static function detectContentType(): string
    {
        // If content-type not set --> fallback to URL encoded
        if (empty($_SERVER['CONTENT_TYPE'])) {
            return CRM_RcBase_Processor_UrlEncodedForm::class;
        }

        // Parse header
        $fields = explode(';', $_SERVER['CONTENT_TYPE']);
        $media_type = trim(array_shift($fields));

        switch ($media_type) {
            case 'application/json':
            case 'application/javascript':
                return CRM_RcBase_Processor_JSON::class;
            case 'text/xml':
            case 'application/xml':
                return CRM_RcBase_Processor_XML::class;
            case 'application/x-www-form-urlencoded':
            default:
                return CRM_RcBase_Processor_UrlEncodedForm::class;
        }
    }

    /**
     * Perform basic input sanitization
     *
     * @param mixed $input Input to sanitize
     *
     * @return mixed Sanitized input
     */
    public static function sanitize($input)
    {
        $sanitized = null;

        // Input is array --> loop through and recurse
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                // Sanitize key
                $key = self::sanitizeString($key);
                // Sanitize value
                $value = self::sanitize($value);

                $sanitized[$key] = $value;
            }
        } elseif (is_string($input)) {
            // Input is string --> sanitize
            $sanitized = self::sanitizeString($input);
        } else {
            // Input is int, float or bool --> no need to sanitize
            $sanitized = $input;
        }

        return $sanitized;
    }

    /**
     * Sanitize string
     *
     * @param mixed $value Value to sanitize
     *
     * @return string Sanitized string
     */
    public static function sanitizeString($value)
    {
        // Strip whitespace
        $value = CRM_Utils_String::stripSpaces($value);
        // Remove quotes around
        $value = preg_replace('/^"(.*)"$/', '$1', $value);
        $value = preg_replace("/^'(.*)'$/", '$1', $value);
        // Remove HTML tags
        $value = preg_replace('/<.*>/U', '', $value);

        return $value;
    }

    /**
     * Validate input
     *
     * Throws exception if problem with input
     * No exception means input OK
     *
     * @param mixed $value Input to validate
     * @param string $type Input type
     *  'string':   any string
     *  'email':    email address
     *  'int':      integer
     *  'id':       positive integer
     *  'float':    float
     *  'bool':     boolean
     *  'date':     date
     *  'datetime': datetime
     * @param string $name Name of variable (for logging and reporting)
     * @param bool $required Is value required?
     *                       throws exception if value is empty
     * @param array $allowed_values Allowed values for this input
     *
     * @return void
     *
     * @throws CRM_Core_Exception
     */
    public static function validateInput(
        $value,
        string $type,
        string $name,
        bool $required = true,
        array $allowed_values = []
    ): void {
        // Check parameters
        if (empty($type)) {
            throw new CRM_Core_Exception('Variable type missing');
        }
        if (empty($name)) {
            throw new CRM_Core_Exception('Variable name missing');
        }

        // Empty value
        if ($value === '' || $value === [] || $value === null) {
            if ($required) {
                throw new CRM_Core_Exception(sprintf('Missing parameter: %s', $name));
            }

            // Value empty and not required --> skip validation
            return;
        }

        switch ($type) {
            case 'string':
                $valid = CRM_Utils_Rule::string($value);
                break;
            case 'email':
                $valid = CRM_Utils_Rule::email($value);
                break;
            case 'int':
                $valid = CRM_Utils_Rule::integer($value);
                break;
            case 'id':
                $valid = CRM_Utils_Rule::positiveInteger($value);
                break;
            case 'float':
                $valid = (is_float($value) || CRM_Utils_Rule::numeric($value));
                break;
            case 'bool':
                $valid = (is_bool($value) || CRM_Utils_Rule::boolean($value));
                break;
            case 'date':
                $valid = CRM_Utils_Rule::date($value);
                break;
            case 'datetime':
                $valid = CRM_Utils_Rule::dateTime($value);
                break;
            case 'datetimeIso':
                $valid = (is_string($value)
                    && (preg_match('/^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d\.\d{1,6}(Z|(\+|-)\d\d:\d\d)$/', $value)));
                break;
            default:
                throw new CRM_Core_Exception(sprintf('Not supported type: %s', $type));
        }

        if (!$valid) {
            throw new CRM_Core_Exception(sprintf('%s is not type of: %s', $name, $type));
        }

        // Allowed values values set --> check
        if (!empty($allowed_values) && !in_array($value, $allowed_values)) {
            throw new CRM_Core_Exception(sprintf('Not allowed value for: %s (%s)', $name, $value));
        }
    }
}
