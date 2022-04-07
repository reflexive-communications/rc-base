<?php

/**
 * Utilities for handling files
 */
class CRM_RcBase_File
{
    /**
     * Check if two files are identical
     * Early return on different file size
     * Then checks SHA-1 hash so there is very slight probability of hash collisions
     *
     * @param string $file_a
     * @param string $file_b
     *
     * @return bool
     *
     * @throws \CRM_Core_Exception
     */
    public static function checkFilesEqual(string $file_a, string $file_b): bool
    {
        if (!is_readable($file_a)) {
            throw new CRM_Core_Exception(sprintf('Failed to read file: "%s"', $file_a));
        }
        if (!is_readable($file_b)) {
            throw new CRM_Core_Exception(sprintf('Failed to read file: "%s"', $file_b));
        }

        if (filesize($file_a) !== filesize($file_b)) {
            return false;
        }

        // Same size --> hash --> check
        return (sha1_file($file_a) === sha1_file($file_b));
    }

    /**
     * Copy file contents to destination
     *
     * @param string $from Source file path
     * @param string $to Destination file path
     *
     * @return bool Success/Failure
     */
    public static function copyFile(string $from, string $to): bool
    {
        if (!is_readable($from)) {
            return false;
        }
        $contents = file_get_contents($from);

        if (!file_put_contents($to, $contents)) {
            return false;
        }

        return true;
    }
}
