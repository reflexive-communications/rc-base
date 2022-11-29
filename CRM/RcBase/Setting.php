<?php

use Civi\Api4\Setting;

/**
 * Settings Wrapper
 *
 * @deprecated use Civi\RcBase\Settings
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class CRM_RcBase_Setting
{
    /**
     * Save setting in DB, create new entry if not exists
     *
     * @param string $name Setting name
     * @param mixed $value Setting value
     *
     * @throws \CRM_Core_Exception
     */
    public static function save(string $name, $value): void
    {
        if (empty($name)) {
            throw new CRM_Core_Exception('Missing setting name');
        }

        Civi::settings()->set($name, $value);

        $saved = Civi::settings()->get($name);
        if ($saved !== $value) {
            throw new CRM_Core_Exception(sprintf('Failed to save setting: %s', $name));
        }
    }

    /**
     * Save secret setting in encrypted format
     *
     * @param string $name Setting name
     * @param string $plain_text Secret value
     *
     * @return void
     * @throws \CRM_Core_Exception
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
     * @throws \CRM_Core_Exception
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
     *
     * @throws CRM_Core_Exception.
     */
    public static function get(string $name)
    {
        if (empty($name)) {
            throw new CRM_Core_Exception('Missing setting name');
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
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function remove(string $name): void
    {
        if (empty($name)) {
            throw new CRM_Core_Exception('Missing setting name');
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

        $dao = new CRM_Core_DAO_Setting();
        $dao->name = $name;
        $dao->delete();
        Civi::service('settings_manager')->flush();

        if (!is_null(Civi::settings()->get($name))) {
            throw new CRM_Core_Exception(sprintf('Failed to delete setting: %s', $name));
        }
    }
}
