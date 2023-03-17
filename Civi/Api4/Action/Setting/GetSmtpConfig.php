<?php

namespace Civi\Api4\Action\Setting;

use Civi;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Setting;
use Civi\RcBase\Api4\ActionUtilsTrait;
use Throwable;

/**
 * Get SMTP config
 *
 * Wrapper for Settings.Get +s mailing_backend, more convenient to use on the CLI,
 * also decrypts encrypted SMTP password.
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class GetSmtpConfig extends AbstractAction
{
    use ActionUtilsTrait;

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
     * Available configs:
     * server (SMTP server URL),
     * port (SMTP server port),
     * user (SMTP username),
     * pass (SMTP password),
     * need_auth (is authentication required).
     *
     * If left empty, it will return all configs.
     *
     * @var string
     */
    protected string $config = '';

    /**
     * @inheritDoc
     */
    public function _run(Result $result): void
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

        if (count($settings) != 1 || !array_key_exists('value', $settings[0])) {
            $result[] = $this->error('Failed to retrieve mailing config');

            return;
        }

        // Decrypt password
        if (array_key_exists('smtpPassword', $settings[0]['value'])) {
            $settings[0]['value']['smtpPassword'] = Civi::service('crypto.token')->decrypt($settings[0]['value']['smtpPassword'], ['plain', 'CRED']);
        }

        // No config -> return all
        if ($this->config === '') {
            foreach (self::CONFIGS_MAP as $name => $civi_name) {
                $configs[$name] = $settings[0]['value'][$civi_name] ?? '';
            }
        } else {
            $configs[$this->config] = $settings[0]['value'][self::CONFIGS_MAP[$this->config]] ?? '';
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
        return $this->config === '' || array_key_exists($this->config, self::CONFIGS_MAP);
    }
}
