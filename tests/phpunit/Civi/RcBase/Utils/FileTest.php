<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class FileTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testTruncate()
    {
        // Create file
        $file = tempnam(sys_get_temp_dir(), 'civi_test_');
        file_put_contents($file, "truncate test\n");
        self::assertSame(14, filesize($file), 'Failed to write file');
        clearstatcache();
        // Truncate
        File::truncate($file);
        self::assertSame(0, filesize($file), 'File not truncated');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testReadLines()
    {
        // Create file
        $text = [
            "This is a test\n",
            "Not much of a test\n",
            "Let's see if lines\n",
            "Are counted fine?\n",
        ];
        $file = tempnam(sys_get_temp_dir(), 'civi_test_');
        foreach ($text as $line) {
            file_put_contents($file, $line, FILE_APPEND);
        }
        // Read file
        $lines = File::readLines($file);
        self::assertCount(4, $lines, 'Wrong number of lines');
        self::assertSame($text, $lines, 'Wrong lines returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testOpen(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'civi_test_');
        $handler = File::open($file);
        self::assertIsResource($handler, 'Failed to open file');
        fclose($handler);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testOpenWithNonExistentFile()
    {
        $file = '/nonexistent/path/to/file';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("{$file} does not exist or is not readable");
        File::open($file);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testOpenWithUnreadableFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'civi_test_');
        // Set file permissions to unreadable
        chmod($file, 0222);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("{$file} does not exist or is not readable");
        File::open($file);
    }
}
