<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\InvalidArgumentException;

/**
 * Utilities for handling files
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class File
{
    /**
     * Truncate file, if not exists create. Much like shell redirection '>filename'
     *
     * @param string $filename File to truncate
     *
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function truncate(string $filename): void
    {
        if (($fp = fopen($filename, 'w')) === false) {
            throw new InvalidArgumentException('file', "Failed to truncate file ${filename}");
        }
        fclose($fp);
    }

    /**
     * Read file line by line
     *
     * @param string $filename File to read
     *
     * @return array Contents of file each line is an array element
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function readLines(string $filename): array
    {
        $lines = [];

        if (($fp = fopen($filename, 'r')) === false) {
            throw new InvalidArgumentException('file', "Failed to read file ${filename}");
        }

        while (($buffer = fgets($fp)) !== false) {
            $lines[] = $buffer;
        }
        fclose($fp);

        return $lines;
    }

    /**
     * Open file for reading
     *
     * @param string $filename File to open (path)
     *
     * @return false|resource
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public static function open(string $filename)
    {
        if (!is_readable($filename)) {
            throw new InvalidArgumentException('file', "${filename} does not exist or is not readable");
        }
        if (($file = fopen($filename, 'r')) === false) {
            throw new InvalidArgumentException('file', "${filename} could not open file for reading");
        }

        return $file;
    }
}
