<?php

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_RcBase_SettingTest extends HeadlessTestCase
{
    /**
     * @return array
     */
    public function provideSettings(): array
    {
        return [
            'string' => ['some string'],
            'integer' => [42],
            'float' => [13.67],
            'bool' => [false],
            'null' => [null],
            'array' => [[1, 'text', 2 => 4, 3 => 'other string']],
            'object' => [new CRM_RcBase_Setting()],
        ];
    }

    /**
     * @dataProvider provideSettings
     * @throws \CRM_Core_Exception
     */
    public function testSave($value)
    {
        $name = 'test-config';
        CRM_RcBase_Setting::save($name, $value);
        $saved = CRM_RcBase_Setting::get($name);
        self::assertSame($value, $saved, 'Wrong value returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testSaveSecret()
    {
        $name = 'secret-key';
        $secret = 'topsecret';
        CRM_RcBase_Setting::saveSecret($name, $secret);

        $raw_value = Civi::settings()->get($name);
        self::assertFalse(Civi::service('crypto.token')->isPlainText($raw_value), 'Secret was not encrypted');

        $saved = CRM_RcBase_Setting::get($name);
        self::assertSame($secret, $saved, 'Wrong secret returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testRotateSecret()
    {
        $name = 'rotate-secret-key';
        $secret = 'original_pass';
        CRM_RcBase_Setting::saveSecret($name, $secret);
        $original_encrypted = Civi::settings()->get($name);
        self::assertFalse(Civi::service('crypto.token')->isPlainText($original_encrypted), 'Secret was not encrypted');

        CRM_RcBase_Setting::rotateSecret($name);
        $rotated_encrypted = Civi::settings()->get($name);
        self::assertFalse(Civi::service('crypto.token')->isPlainText($rotated_encrypted), 'Secret was not encrypted');

        self::assertNotEquals($original_encrypted, $rotated_encrypted, 'Secret not rotated');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testGetNonExistentReturnsNull()
    {
        self::assertNull(CRM_RcBase_Setting::get('non_existent_setting'));
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testRemove()
    {
        self::assertEmpty(CRM_RcBase_Setting::save('remove-existent-setting', true));
        self::assertEmpty(CRM_RcBase_Setting::remove('remove-existent-setting'));

        self::assertEmpty(CRM_RcBase_Setting::remove('remove-non-existent-setting'));
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testMissingNameThrowsExceptionOnSave()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing setting name');
        CRM_RcBase_Setting::save('', 43);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testMissingNameThrowsExceptionOnGet()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing setting name');
        CRM_RcBase_Setting::get('');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testMissingNameThrowsExceptionOnRemove()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing setting name');
        CRM_RcBase_Setting::remove('');
    }
}
