<?php

namespace Civi\RcBase;

use API_Exception;
use Civi;
use Civi\Api4\Setting;
use Civi\RcBase\Exception\MissingArgumentException;
use stdClass;

/**
 * @group headless
 */
class SettingsTest extends HeadlessTestCase
{
    /**
     * @return array
     */
    public function provideSettings(): array
    {
        $object = new stdClass();
        $object->property = 'value';

        return [
            'string' => ['some string'],
            'integer' => [42],
            'float' => [13.67],
            'bool' => [false],
            'null' => [null],
            'array' => [[1, 'text', 2 => 4, 3 => 'other string']],
            'object' => [$object],
        ];
    }

    /**
     * @dataProvider provideSettings
     * @throws \CRM_Core_Exception
     */
    public function testSave($value)
    {
        $name = 'test-config';
        Settings::save($name, $value);
        $saved = Settings::get($name);
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
        Settings::saveSecret($name, $secret);

        $raw_value = Civi::settings()->get($name);
        self::assertTrue(Settings::isEncrypted($raw_value), 'Secret was not encrypted');

        $saved = Settings::get($name);
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
        Settings::saveSecret($name, $secret);
        $cipher_text_original = Civi::settings()->get($name);
        self::assertTrue(Settings::isEncrypted($cipher_text_original), 'Secret was not encrypted');

        // Add new encryption key & rotate secrets
        Civi::service('crypto.registry')->addSymmetricKey([
            'key' => '12345678901234567890123456789012',
            'suite' => 'aes-cbc',
            'tags' => ['CRED'],
            'weight' => -1,
        ]);
        Settings::rotateSecret($name);

        $cipher_text_rotated = Civi::settings()->get($name);
        self::assertTrue(Settings::isEncrypted($cipher_text_rotated), 'Secret was not encrypted');
        self::assertNotEquals($cipher_text_original, $cipher_text_rotated, 'Secret not rotated');

        // Rotate non-existent secret
        Settings::rotateSecret('non-existent-secret-key');
        self::assertNull(Civi::settings()->get('non-existent-secret-key'), 'Non-existent secret created');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testGetNonExistentReturnsNull()
    {
        self::assertNull(Settings::get('non_existent_setting'));
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testRemove()
    {
        $setting = 'test-setting';

        // Remove existent setting
        $caught = false;
        Settings::save($setting, true);
        Settings::remove($setting);
        try {
            Setting::get()
                ->addSelect($setting)
                ->execute();
        } catch (API_Exception $ex) {
            self::assertStringContainsString("Unknown settings for domain 1: {$setting}", $ex->getMessage());
            $caught = true;
        }
        self::assertTrue($caught, 'Setting not deleted');

        // Remove again now non-existent setting
        $caught = false;
        Settings::remove($setting);
        try {
            Setting::get()
                ->addSelect($setting)
                ->execute();
        } catch (API_Exception $ex) {
            self::assertStringContainsString("Unknown settings for domain 1: {$setting}", $ex->getMessage());
            $caught = true;
        }
        self::assertTrue($caught, 'Setting not deleted');
    }

    /**
     * @return void
     */
    public function testIsEncrypted()
    {
        $plain_text = 'plain';
        $cipher_text = Settings::encrypt($plain_text);
        self::assertFalse(Settings::isEncrypted(''), 'Empty string reported as encrypted');
        self::assertFalse(Settings::isEncrypted($plain_text), 'Non-encrypted setting reported as encrypted');
        self::assertTrue(Settings::isEncrypted($cipher_text), 'Encrypted setting reported as non-encrypted');
    }

    /**
     * @return void
     */
    public function testEncrypt()
    {
        $value = 'secret-api-key';

        $encrypted = Settings::encrypt($value);
        self::assertNotSame($value, $encrypted, 'Secret not encrypted');
        self::assertTrue(Settings::isEncrypted($encrypted), 'Secret was not encrypted');

        $decrypted = Settings::decrypt($encrypted);
        self::assertSame($value, $decrypted, 'Secret not decrypted');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testMissingNameThrowsExceptionOnSave()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('setting name');
        Settings::save('', 43);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testMissingNameThrowsExceptionOnGet()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('setting name');
        Settings::get('');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testMissingNameThrowsExceptionOnRemove()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('setting name');
        Settings::remove('');
    }

    /**
     * @return void
     */
    public function testCache()
    {
        Civi::cache()->clear();

        // Short-lived cache
        $key = 'short-cache';
        foreach (['some-string', true, null, ['key' => ['sub-array' => 'value']]] as $value) {
            self::assertNull(Settings::cacheGet($key), 'Cached value not null');
            self::assertFalse(Settings::cacheHas($key), 'Cache should not have key');
            Settings::cacheSet($key, $value);
            self::assertSame($value, Settings::cacheGet($key), 'Wrong cached value');
            self::assertTrue(Settings::cacheHas($key), 'Cache should have key');

            // Delete entry for next iteration
            Civi::cache('short')->delete($key);
        }

        // Long-lived cache
        $key = 'long-cache';
        foreach (['some-string', true, null, ['key' => ['sub-array' => 'value']]] as $value) {
            self::assertNull(Settings::cacheGet($key, 'long'), 'Cached value not null');
            self::assertFalse(Settings::cacheHas($key, 'long'), 'Cache should not have key');
            Settings::cacheSet($key, $value, 'long');
            self::assertSame($value, Settings::cacheGet($key, 'long'), 'Wrong cached value');
            self::assertTrue(Settings::cacheHas($key, 'long'), 'Cache should have key');

            // Delete entry for next iteration
            Civi::cache('long')->delete($key);
        }

        // Cache save
        $key = 'cache-save';
        $cache_save_counter = 0;
        $get_value = function () use (&$cache_save_counter) {
            return ++$cache_save_counter;
        };

        $value = Settings::cacheSave($key, $get_value);
        self::assertSame(1, $value, 'Wrong cached value');
        self::assertTrue(Settings::cacheHas($key), 'Cache should have key');

        $value = Settings::cacheSave($key, $get_value);
        self::assertSame(1, $value, 'get_value should not be called again');
    }
}
