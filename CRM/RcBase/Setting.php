<?php

use Civi\Api4\Setting;

/**
 * Settings Wrapper
 *
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

        return Civi::settings()->get($name);
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
    public static function remove(string $name)
    {
        if (empty($name)) {
            throw new CRM_Core_Exception('Missing setting name');
        }

        // Search setting
        $exists = false;
        $settings = Setting::get()->execute();
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
