<?php

namespace Civi\RcBase\IOProcessor;

use Civi;
use Civi\Core\Service\AutoService;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\Exception\RunTimeException;
use CRM_Utils_Rule;
use CRM_Utils_String;
use Throwable;

/**
 * Base IO Processor
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 * @service
 * @internal
 */
abstract class Base extends AutoService implements IOProcessorInterface
{
    /**
     * Parse input from stream wrappers
     * Example:
     *   - http: $stream="https://example.com/json"
     *   - file: $stream="file:///path/to/local/file"
     *   - data: $stream="data://text/plain;base64,bW9ua2V5Cg=="
     *   - php:  $stream="php://input"
     *
     * @link https://www.php.net/manual/en/wrappers.expect.php
     *
     * @param string $stream Name of input stream
     *
     * @return mixed Parsed data
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public function decodeStream(string $stream)
    {
        try {
            $raw = file_get_contents($stream);
        } catch (Throwable $ex) {
            throw new RunTimeException('Failed to open stream: '.$ex->getMessage(), $ex);
        }

        return $this->decode($raw);
    }

    /**
     * Parse input from POST request body
     *
     * @return mixed Parsed data
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public function decodePost()
    {
        return $this->decodeStream('php://input');
    }

    /**
     * Return appropriate IO Processor service based on request content-type
     *
     * @return \Civi\RcBase\IOProcessor\IOProcessorInterface
     */
    public static function getIOProcessorService(): IOProcessorInterface
    {
        if (empty($_SERVER['CONTENT_TYPE'] ?? '')) {
            return Civi::service('IOProcessor.UrlEncodedForm');
        }

        $fields = explode(';', $_SERVER['CONTENT_TYPE']);
        $media_type = trim(array_shift($fields));

        switch ($media_type) {
            case 'application/json':
            case 'application/javascript':
                return Civi::service('IOProcessor.JSON');
            case 'text/xml':
            case 'application/xml':
                return Civi::service('IOProcessor.XML');
            case 'application/x-www-form-urlencoded':
            default:
                return Civi::service('IOProcessor.UrlEncodedForm');
        }
    }

    /**
     * Detect content-type
     *
     * @return string Relevant Processor class name
     */
    public static function detectContentType(): string
    {
        // If content-type not set --> fallback to URL encoded
        if (empty($_SERVER['CONTENT_TYPE'])) {
            return UrlEncodedForm::class;
        }

        // Parse header
        $fields = explode(';', $_SERVER['CONTENT_TYPE']);
        $media_type = trim(array_shift($fields));

        switch ($media_type) {
            case 'application/json':
            case 'application/javascript':
                return JSON::class;
            case 'text/xml':
            case 'application/xml':
                return XML::class;
            case 'application/x-www-form-urlencoded':
            default:
                return UrlEncodedForm::class;
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
     * @param mixed $string Value to sanitize
     *
     * @return string Sanitized string
     */
    public static function sanitizeString($string): string
    {
        return CRM_Utils_String::purifyHTML(CRM_Utils_String::stripSpaces($string));
    }

    /**
     * Validate input
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
     *  'datetimeIso': datetime ISO format 'Y-m-d\T
     * @param string $name Name of variable (for logging and reporting)
     * @param bool $required Is value required?
     *                       throws exception if value is empty
     * @param array $allowed_values Allowed values for this input
     *
     * @return mixed Value casted to correct type
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function validateInput($value, string $type, string $name, bool $required = true, array $allowed_values = [])
    {
        if (empty($type)) {
            throw new MissingArgumentException('type');
        }
        if (empty($name)) {
            throw new MissingArgumentException('name');
        }

        if ($value === '' || $value === [] || $value === null) {
            if ($required) {
                throw new InvalidArgumentException($name, 'Missing required parameter');
            }

            // Value empty and not required --> skip validation
            return $value;
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
                if ($valid) {
                    $value = (int)$value;
                }
                break;
            case 'id':
                $valid = CRM_Utils_Rule::positiveInteger($value);
                if ($valid) {
                    $value = (int)$value;
                }
                break;
            case 'float':
                $valid = (is_float($value) || CRM_Utils_Rule::numeric($value));
                if ($valid) {
                    $value = (float)$value;
                }
                break;
            case 'bool':
                $valid = (is_bool($value) || CRM_Utils_Rule::boolean($value));
                if ($valid) {
                    $value = (bool)$value;
                }
                break;
            case 'date':
                $valid = CRM_Utils_Rule::date($value);
                break;
            case 'datetime':
                $valid = CRM_Utils_Rule::dateTime($value);
                break;
            case 'datetimeIso':
                $valid = (is_string($value) && (preg_match('/^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d\.\d{1,6}(Z|([+-])\d\d:\d\d)$/', $value)));
                break;
            default:
                throw new InvalidArgumentException('type', 'Not supported type');
        }

        if (!$valid) {
            throw new InvalidArgumentException($name, "{$name} is not {$type} (value: ".var_export($value, true).')');
        }

        // Allowed values values set --> check
        if (!empty($allowed_values) && !in_array($value, $allowed_values)) {
            throw new InvalidArgumentException($name, 'Not allowed value for');
        }

        return $value;
    }
}
