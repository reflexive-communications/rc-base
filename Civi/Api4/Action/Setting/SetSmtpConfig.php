<?php

namespace Civi\Api4\Action\Setting;

use Civi;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Setting;
use Throwable;

/**
 * Set SMTP configs
 *
 * Wrapper for Setting.set +v mailing_backend={config_json}. It is designed for easier use on the CLI.
 * Also encrypts the SMTP password.
 */
class SetSmtpConfig extends AbstractAction
{
    /**
     * SMTP server URL
     *
     * @var string
     */
    protected string $server = '';

    /**
     * SMTP server port
     *
     * @var int
     */
    protected int $port = 0;

    /**
     * SMTP user
     *
     * @var string
     */
    protected string $user = '';

    /**
     * SMTP password
     *
     * @var string
     */
    protected string $pass = '';

    /**
     * Need to authenticate on SMTP server
     *
     * @var bool
     */
    protected ?bool $needAuth = null;


    /**
     * @inheritDoc
     */
    public function _run(Result $result)
    {
        $configs = [];

        if (!empty($this->server)) {
            $configs['smtpServer'] = $this->server;
        }
        if (!empty($this->port)) {
            $configs['smtpPort'] = $this->port;
        }
        if (!empty($this->user)) {
            $configs['smtpUsername'] = $this->user;
        }
        if (!empty($this->pass)) {
            // Encrypt password
            $configs['smtpPassword'] = Civi::service('crypto.token')->encrypt($this->pass, 'CRED');
        }
        if (!is_null($this->needAuth)) {
            $configs['smtpAuth'] = $this->needAuth;
        }

        // Nothing to set
        if (empty($configs)) {
            return;
        }

        try {
            $results = Setting::set()
                ->addValue('mailing_backend', $configs)
                ->execute();
        } catch (Throwable $ex) {
            $result[] = $this->error($ex->getMessage());
            return;
        }

        if (count($results) != 1) {
            $result[] = $this->error('Failed to set mailing config');
            return;
        }

        $result[] = $configs;
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
