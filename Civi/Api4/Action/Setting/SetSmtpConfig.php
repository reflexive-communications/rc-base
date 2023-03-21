<?php

namespace Civi\Api4\Action\Setting;

use API_Exception;
use Civi;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Setting;
use Civi\RcBase\Api4\ActionUtilsTrait;
use Throwable;

/**
 * Set SMTP configs
 *
 * Wrapper for Setting.set +v mailing_backend={config_json}. It is designed for easier use on the CLI.
 * Also encrypts the SMTP password.
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class SetSmtpConfig extends AbstractAction
{
    use ActionUtilsTrait;

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
    public function _run(Result $result): void
    {
        $new = [];

        if (!empty($this->server)) {
            $new['smtpServer'] = $this->server;
        }
        if (!empty($this->port)) {
            $new['smtpPort'] = (string)$this->port;
        }
        if (!empty($this->user)) {
            $new['smtpUsername'] = $this->user;
        }
        if (!empty($this->pass)) {
            // Encrypt password
            $new['smtpPassword'] = Civi::service('crypto.token')->encrypt($this->pass, 'CRED');
        }
        if (!is_null($this->needAuth)) {
            if ($this->needAuth) {
                $new['smtpAuth'] = '1';
            } else {
                $new['smtpAuth'] = '0';
            }
        }

        // Nothing to set
        if (empty($new)) {
            return;
        }

        try {
            $configs = Setting::get(false)
                ->addSelect('mailing_backend')
                ->execute();
            if (count($configs) != 1 || !array_key_exists('value', $configs[0])) {
                throw new API_Exception('Failed to get old configs');
            }
            $old = $configs[0]['value'];

            $merged = array_merge($old, $new);

            // If both passwords are encrypted, we have to compare the plain-text passwords
            // Ciphertext passwords always differ, even if plain-texts are the same
            $old_pass = $old['smtpPassword'] ?? '';
            $merged_pass = $merged['smtpPassword'] ?? '';
            if (($old_pass != $merged_pass)
                && !Civi::service('crypto.token')->isPlainText($old_pass)
                && !Civi::service('crypto.token')->isPlainText($merged_pass)
                && (Civi::service('crypto.token')->decrypt($old_pass) == Civi::service('crypto.token')->decrypt($merged_pass))
            ) {
                // Same plain-text password -> use old value (avoid unnecessary change, even if it would have the same effect)
                if ($old_pass != '') {
                    $merged['smtpPassword'] = $old_pass;
                }
            }

            // Merging new values have not changed the array -> there is no change
            if ($old === $merged) {
                $result[] = ['no_change' => true];

                return;
            }

            $results = Setting::set()
                ->addValue('mailing_backend', $merged)
                ->execute();
        } catch (Throwable $ex) {
            $result[] = $this->error($ex->getMessage());

            return;
        }

        if (count($results) != 1) {
            $result[] = $this->error('Failed to set mailing config');

            return;
        }

        $result[] = $merged;
    }
}
