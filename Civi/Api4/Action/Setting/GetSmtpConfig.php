<?php

namespace Civi\Api4\Action\Setting;

use Civi;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Setting;
use Throwable;

/**
 * Get SMTP configs
 * Wrapper for Settings.Get +s mailing_backend
 */
class GetSmtpConfig extends AbstractAction
{
    /**
     * Map configs to Civi config names
     */
    public const CONFIGS_MAP
        = [
            'server' => 'smtpServer',
            'port' => 'smtpPort',
            'user' => 'smtpUsername',
            'pass' => 'smtpPassword',
            'need_auth' => 'smtpAuth',
        ];

    /**
     * Name of config to return
     *
     * @var string
     */
    protected $config = '';

    /**
     * @inheritDoc
     */
    public function _run(Result $result)
    {
        $configs = [];

        if (!$this->validateParams()) {
            $result[] = $this->error(sprintf('Not allowed config: %s', $this->config));
            return;
        }

        try {
            $settings = Setting::get()
                ->addSelect('mailing_backend')
                ->execute();
        } catch (Throwable $ex) {
            $result[] = $this->error($ex->getMessage());
            return;
        }

        if (count($settings) != 1) {
            $result[] = $this->error('Failed to retrieve mailing config');
            return;
        }

        // Decrypt password
        $settings[0]['value']['smtpPassword'] = Civi::service('crypto.token')->decrypt($settings[0]['value']['smtpPassword'], ['plain', 'CRED']);

        // No config -> return all
        if ($this->config === '') {
            foreach (self::CONFIGS_MAP as $name => $civi_name) {
                $configs[$name] = $settings[0]['value'][$civi_name];
            }
        } else {
            $configs[$this->config] = $settings[0]['value'][self::CONFIGS_MAP[$this->config]];
        }

        $result[] = $configs;
    }

    /**
     * Validate parameters
     *
     * @return bool Success
     */
    protected function validateParams(): bool
    {
        if ($this->config === '' || array_key_exists($this->config, self::CONFIGS_MAP)) {
            return true;
        }
        return false;
    }

    /**
     * Format error message
     *
     * @param string $message Error message
     *
     * @return array
     */
    protected function error(string $message): array
    {
        return [
            'is_error' => true,
            'error_message' => $message,
        ];
    }
}
