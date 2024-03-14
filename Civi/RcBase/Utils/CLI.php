<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\MissingArgumentException;

/**
 * Utility class for CLI applications
 */
class CLI
{
    /**
     * Parse command-line arguments
     * Similar to getopt() but with limited capabilities:
     *   - Arguments need to be passed as separate array elements (they must be separated by whitespace on the command-line)
     *   - Only supports options with required value (r:, [req:]) or flag-type options that doesn't accept a value (f, [flag])
     *
     * @param array $arguments Command-line arguments (usually $argv)
     * @param string $options_short Short options (e.g. 'fr:')
     * @param array $options_long Long options (e.g. ['option-with-value:', 'flag'])
     *
     * @return array
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function parseArguments(array $arguments, string $options_short = '', array $options_long = []): array
    {
        $options_parsed = [];
        $arguments_parsed = [];

        if (empty($arguments)) {
            return $arguments_parsed;
        }

        // Parse short options
        $options_short = preg_replace('/[^a-zA-Z0-9:]/', '', $options_short);
        $options_short = str_split($options_short);
        while (true) {
            $option = array_shift($options_short);
            if ($option === '' || $option === null) {
                break;
            }
            if ($option == ':') {
                continue;
            }

            // Check next option character to see if option requires a value or not (flag-type option)
            $options_parsed['-'.$option] = [
                'name' => $option,
                'required' => ($options_short[0] ?? '') == ':',
            ];
        }

        // Parse long options
        while (true) {
            $option = array_shift($options_long);
            $option = preg_replace('/[^a-zA-Z0-9:-]/', '', $option);
            if ($option === '' || $option === null) {
                break;
            }
            if (!preg_match('/^[a-zA-Z0-9-]+:?$/', $option)) {
                continue;
            }

            // Check last option character to see if option requires a value or not (flag-type option)
            $required = false;
            if (substr($option, -1) == ':') {
                $option = substr($option, 0, -1);
                $required = true;
            }

            $options_parsed['--'.$option] = [
                'name' => $option,
                'required' => $required,
            ];
        }

        // Consume arguments
        while (true) {
            $argument = array_shift($arguments);
            if ($argument === '' || $argument === null) {
                break;
            }
            if (!array_key_exists($argument, $options_parsed)) {
                continue;
            }

            // If argument requires a value, get it from the next argument
            if ($options_parsed[$argument]['required']) {
                $value = array_shift($arguments);
                if ($value === '' || $value === null) {
                    throw new MissingArgumentException($argument, 'option value is not passed');
                }
                $arguments_parsed[$options_parsed[$argument]['name']] = $value;
            } else {
                $arguments_parsed[$options_parsed[$argument]['name']] = true;
            }
        }

        return $arguments_parsed;
    }

    /**
     * Get a color escape sequence
     *
     * @param string $color Color name
     *   Colors:
     *     - normal
     *     - bold
     *     - red
     *     - green
     *     - yellow
     *
     * @return string
     */
    public static function color(string $color): string
    {
        switch ($color) {
            case 'normal':
                $arg = 'sgr0';
                break;
            case 'bold':
                $arg = 'bold';
                break;
            case 'red':
                $arg = 'setaf 1';
                break;
            case 'green':
                $arg = 'setaf 2';
                break;
            case 'yellow':
                $arg = 'setaf 3';
                break;
            default:
                return '';
        }

        return `tput {$arg}`;
    }

    /**
     * Print a message to stdout
     *
     * @param string $message Message to print
     *
     * @return void
     */
    public static function print(string $message): void
    {
        echo $message;
    }

    /**
     * Print a message to stdout and append a newline
     *
     * @param string $message Message to print
     *
     * @return void
     */
    public static function printLine(string $message = ''): void
    {
        self::print($message."\n");
    }

    /**
     * Print a message to stderr
     *
     * @param string $message Error message to print
     *
     * @return void
     */
    public static function printError(string $message): void
    {
        fwrite(STDERR, self::color('red').self::color('bold').$message.self::color('normal')."\n");
    }

    /**
     * Print a header
     *
     * @param string $header Header to print
     *
     * @return void
     */
    public static function printHeader(string $header): void
    {
        self::printLine(self::color('yellow').$header.self::color('normal'));
    }

    /**
     * Print a status message
     *
     * @param string $status Status message
     *
     * @return void
     */
    public static function printStatus(string $status): void
    {
        self::print(self::color('yellow').$status.self::color('normal'));
    }

    /**
     * Print OK status message
     *
     * @param string $message Message to print
     *
     * @return void
     */
    public static function printFinish(string $message = 'Done.'): void
    {
        self::printLine(self::color('green').self::color('bold').$message.self::color('normal'));
    }
}
