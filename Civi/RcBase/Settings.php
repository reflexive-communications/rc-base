<?php

namespace Civi\RcBase;

use Civi;
use Civi\Api4\Setting;
use Civi\RcBase\Exception\DataBaseException;
use Civi\RcBase\Exception\MissingArgumentException;
use CRM_Core_DAO_Setting;

/**
 * Settings Wrapper
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Settings
{
    /**
     * Save setting in DB, create new entry if not exists
     *
     * @param string $name Setting name
     * @param mixed $value Setting value
     *
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function save(string $name, $value): void
    {
        if (empty($name)) {
            throw new MissingArgumentException('setting name');
        }

        Civi::settings()->set($name, $value);

        $saved = Civi::settings()->get($name);
        if ($saved !== $value) {
            throw new DataBaseException("Failed to save setting: {$name}");
        }
    }

    /**
     * Save secret setting in encrypted format
     *
     * @param string $name Setting name
     * @param string $plain_text Secret value
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function saveSecret(string $name, string $plain_text): void
    {
        self::save($name, self::encrypt($plain_text));
    }

    /**
     * Rotate secret value
     *
     * @param string $name Setting name
     *
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function rotateSecret(string $name): void
    {
        $cipher_text = Civi::settings()->get($name);
        if (is_null($cipher_text)) {
            return;
        }

        self::save($name, self::reencrypt($cipher_text));
    }

    /**
     * Get setting value from DB
     *
     * @param string $name Setting name
     *
     * @return mixed Setting value
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function get(string $name)
    {
        if (empty($name)) {
            throw new MissingArgumentException('setting name');
        }

        $value = Civi::settings()->get($name);

        // Decrypt if needed
        if (is_string($value) && !Civi::service('crypto.token')->isPlainText($value)) {
            return self::decrypt($value);
        }

        return $value;
    }

    /**
     * Remove setting from DB
     *
     * @param string $name Setting name
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public static function remove(string $name): void
    {
        if (empty($name)) {
            throw new MissingArgumentException('setting name');
        }

        // Search setting
        $exists = false;
        $settings = Setting::get(false)->execute();
        foreach ($settings as $setting) {
            if ($setting['name'] == $name) {
                $exists = true;
                break;
            }
        }

        // Not found --> job done
        if (!$exists) {
            return;
        }

        // API not available --> use DAO
        $dao = new CRM_Core_DAO_Setting();
        $dao->name = $name;
        $dao->delete();
        Civi::service('settings_manager')->flush();

        if (!is_null(Civi::settings()->get($name))) {
            throw new DataBaseException("Failed to delete setting: {$name}");
        }
    }

    /**
     * Check if text is encrypted
     *
     * @param string $text Text to check
     *
     * @return bool
     */
    public static function isEncrypted(string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        return !Civi::service('crypto.token')->isPlainText($text);
    }

    /**
     * Encrypt secret
     *
     * @param string $plain_text Plain text
     *
     * @return string Cipher text
     */
    public static function encrypt(string $plain_text): string
    {
        return Civi::service('crypto.token')->encrypt($plain_text, 'CRED');
    }

    /**
     * Decrypt secret
     *
     * @param string $cipher_text Cipher text
     *
     * @return string Plain text
     */
    public static function decrypt(string $cipher_text): string
    {
        return Civi::service('crypto.token')->decrypt($cipher_text, ['plain', 'CRED']);
    }

    /**
     * Re-encrypt cipher text
     *
     * @param string $cipher_text
     *
     * @return string
     */
    public static function reencrypt(string $cipher_text): string
    {
        $rotated = Civi::service('crypto.token')->rekey($cipher_text, 'CRED');

        // CryptoToken::rekey() returns null if no need to rotate (key not changed)
        // In that case return old cipher text to avoid surprises downstream
        return is_null($rotated) ? $cipher_text : $rotated;
    }

    /**
     * Get value from cache
     *
     * @param string $key Cache entry key
     * @param string $cache Which cache to query?
     *
     * @return mixed
     */
    public static function cacheGet(string $key, string $cache = 'short')
    {
        return Civi::cache($cache)->get($key);
    }

    /**
     * Set value in cache
     *
     * @param string $key Cache entry key
     * @param mixed $value Value to store
     * @param string $cache Which cache to use?
     *
     * @return void
     */
    public static function cacheSet(string $key, $value, string $cache = 'short'): void
    {
        Civi::cache($cache)->set($key, $value);
    }

    /**
     * Check entry is in cache
     *
     * @param string $key Cache entry key
     * @param string $cache Which cache to query?
     *
     * @return bool
     */
    public static function cacheHas(string $key, string $cache = 'short'): bool
    {
        return Civi::cache($cache)->has($key);
    }
}
