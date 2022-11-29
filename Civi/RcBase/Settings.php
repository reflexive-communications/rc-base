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
        $encrypted = Civi::service('crypto.token')->encrypt($plain_text, 'CRED');
        self::save($name, $encrypted);
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
        $rekeyed = Civi::service('crypto.token')->rekey(self::get($name), 'CRED');
        if (!is_null($rekeyed)) {
            self::save($name, $rekeyed);
        }
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
            return Civi::service('crypto.token')->decrypt($value, ['plain', 'CRED']);
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
}