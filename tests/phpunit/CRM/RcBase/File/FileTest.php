<?php

use PHPUnit\Framework\TestCase;

/**
 * Test CRM_RcBase_File
 */
class CRM_RcBase_File_FileTest extends TestCase
{
    public function provideFiles(): array
    {
        return [
            'identical files' => [__DIR__.'/file_1', __DIR__.'/file_1', true],
            'different files' => [__DIR__.'/file_1', __DIR__.'/file_2', false],
            'same size, different content' => [__DIR__.'/file_1', __DIR__.'/file_1b', false],
        ];
    }

    /**
     * @dataProvider provideFiles
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
}
