<?php

use PHPUnit\Framework\TestCase;

/**
 * Test CRM_RcBase_File
 *
 * @group unit
 */
class CRM_RcBase_File_FileTest extends TestCase
{
    public function provideFilesToCheck(): array
    {
        return [
            'identical files' => [__DIR__.'/file_1', __DIR__.'/file_1', true],
            'different files' => [__DIR__.'/file_1', __DIR__.'/file_2', false],
            'same size, different content' => [__DIR__.'/file_1', __DIR__.'/file_1b', false],
        ];
    }

    /**
     * @dataProvider provideFilesToCheck
     *
     * @param $file_a
     * @param $file_b
     * @param $expected
     *
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testCheckFilesEqual($file_a, $file_b, $expected)
    {
        self::assertSame($expected, CRM_RcBase_File::checkFilesEqual($file_a, $file_b));
    }

    public function testCheckFilesEqualWithUnreadableFileThrowsException()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Failed to read file');
        CRM_RcBase_File::checkFilesEqual(__DIR__.'/non-existent.file', __DIR__.'/non-existent.file');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testCopyFiles()
    {
        $source = __DIR__.'/file_1';
        $target = __DIR__.'/copy_file_'.time();
        self::assertTrue(CRM_RcBase_File::copyFile($source, $target), 'Failed to copy files');
        self::assertTrue(CRM_RcBase_File::checkFilesEqual($source, $target), 'Source and target differ after copy');
    }

    public function testCopyFilesWithUnreadableSource()
    {
        $source = __DIR__.'/non-existent.file';
        $target = __DIR__.'/copy_file_'.time();
        self::assertFalse(CRM_RcBase_File::copyFile($source, $target), 'Not false returned on unreadable source file');
    }
}
