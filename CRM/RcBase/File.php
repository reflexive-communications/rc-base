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
        $files = [$file_a, $file_b];
        foreach ($files as $file) {
            if (!is_readable($file)) {
                throw new CRM_Core_Exception(sprintf('Failed to read file: "%s"', $file));
            }
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
     * @param string $source Source file path
     * @param string $target Destination file path
     *
     * @return bool Success/Failure
     */
    public static function copyFile(string $source, string $target): bool
    {
        if (!is_readable($source)) {
            return false;
        }
        $contents = file_get_contents($source);

        if (file_put_contents($target, $contents) === false) {
            return false;
        }

        return true;
    }
}
