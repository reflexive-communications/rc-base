<?php

use Civi\Api4\Setting;

/**
 * Test Get/Set SMTP Config API
 *
 * @group headless
 */
class CRM_RcBase_Api4_SmtpConfigTest extends CRM_RcBase_HeadlessTestCase
{
    public const DEFAULT_SMTP_CONFIG
        = [
            'smtpServer' => 'smtp.example.com',
            'smtpPort' => '465',
            'smtpAuth' => '0',
            'smtpUsername' => 'admin',
            'smtpPassword' => 'pass',
        ];

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function setUp(): void
    {
        $results = Setting::set()
            ->addValue('mailing_backend', self::DEFAULT_SMTP_CONFIG)
            ->execute();
        self::assertCount(1, $results, 'Failed to set default config');
    }

    /**
     * Return current mailing settings
     *
     * @return array
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function getMailingSettings(): array
    {
        $settings = Setting::get()
            ->addSelect('mailing_backend')
            ->execute();
        self::assertCount(1, $settings, 'Failed to get configs');
        self::assertArrayHasKey('value', $settings[0], 'Failed to get configs');

        return $settings[0]['value'];
    }

    /**
     * @return void
     */
    public function testGetAllConfig()
    {
        $smtp_configs = Setting::getSmtpConfig()->execute();

        self::assertCount(1, $smtp_configs, 'Failed to get configs');
        foreach (Civi\Api4\Action\Setting\GetSmtpConfig::CONFIGS_MAP as $name => $civi_name) {
            self::assertArrayHasKey($name, $smtp_configs[0], "Missing config: ${name}");
            self::assertEquals(self::DEFAULT_SMTP_CONFIG[$civi_name], $smtp_configs[0][$name]);
        }
    }

    /**
     * @return void
     */
    public function testGetOneConfig()
    {
        foreach (Civi\Api4\Action\Setting\GetSmtpConfig::CONFIGS_MAP as $name => $civi_name) {
            $smtp_configs = Setting::getSmtpConfig()
                ->setConfig($name)
                ->execute();
            self::assertCount(1, $smtp_configs, 'Failed to get configs');
            self::assertArrayHasKey($name, $smtp_configs[0], "Missing config: ${name}");
            self::assertEquals(self::DEFAULT_SMTP_CONFIG[$civi_name], $smtp_configs[0][$name]);
        }
    }

    /**
     * @return void
     */
    public function testGetInvalidNameReturnsError()
    {
        $smtp_configs = Setting::getSmtpConfig()
            ->setConfig('invalid_config')
            ->execute();
        self::assertCount(1, $smtp_configs, 'Failed to get configs');
        self::assertArrayHasKey('is_error', $smtp_configs[0], 'Missing is_error');
        self::assertArrayHasKey('error_message', $smtp_configs[0], 'Missing error_message');
        self::assertEquals(true, $smtp_configs[0]['is_error']);
        self::assertEquals('Not allowed config: invalid_config', $smtp_configs[0]['error_message']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetConfig()
    {
        $new_configs = [
            'server' => 'other.smtp.example.com',
            'port' => 112,
            'need_auth' => true,
            'user' => 'other_admin',
            'pass' => 'other_pass',
        ];

        Setting::setSmtpConfig()
            ->setServer($new_configs['server'])
            ->setPort($new_configs['port'])
            ->setUser($new_configs['user'])
            ->setPass($new_configs['pass'])
            ->setNeedAuth($new_configs['need_auth'])
            ->execute();

        $smtp_configs = $this->getMailingSettings();
        self::assertArrayHasKey('smtpServer', $smtp_configs, 'Missing config: smtpServer');
        self::assertEquals($new_configs['server'], $smtp_configs['smtpServer']);
        self::assertArrayHasKey('smtpPort', $smtp_configs, 'Missing config: smtpPort');
        self::assertEquals($new_configs['port'], $smtp_configs['smtpPort']);
        self::assertArrayHasKey('smtpAuth', $smtp_configs, 'Missing config: smtpAuth');
        self::assertEquals($new_configs['need_auth'], $smtp_configs['smtpAuth']);
        self::assertArrayHasKey('smtpUsername', $smtp_configs, 'Missing config: smtpUsername');
        self::assertEquals($new_configs['user'], $smtp_configs['smtpUsername']);
    }

    /**
     * @return void
     */
    public function testSetNoParamsReturnEmpty()
    {
        $results = Setting::setSmtpConfig()
            ->execute();
        self::assertCount(0, $results, 'Not empty result set when no params to set');
    }

    /**
     * @return void
     */
    public function testPasswordChange()
    {
        $new_pass = 'new_password';
        Setting::setSmtpConfig()
            ->setPass($new_pass)
            ->execute();

        $pass = Setting::getSmtpConfig()
            ->setConfig('pass')
            ->execute();
        self::assertCount(1, $pass, 'Failed to get password');
        self::assertArrayHasKey('pass', $pass[0], 'Failed to get password');
        self::assertArrayNotHasKey('no_change', $pass[0], 'Password was not changed');
        self::assertEquals($new_pass, $pass[0]['pass'], 'Failed to change password');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetSameValuesReturnNoChange()
    {
        // Set password, if encryption is enabled there will be change
        // As the default is not encrypted, and `setSmtpConfig` encrypts
        $changed = Setting::setSmtpConfig()
            ->setPass(self::DEFAULT_SMTP_CONFIG['smtpPassword'])
            ->execute();
        self::assertCount(1, $changed, 'Failed to set password');

        // Set password again, now there should be no change
        $settings_before = $this->getMailingSettings();
        $changed = Setting::setSmtpConfig()
            ->setPass(self::DEFAULT_SMTP_CONFIG['smtpPassword'])
            ->execute();
        $settings_after = $this->getMailingSettings();
        self::assertCount(1, $changed, 'Failed to set password');
        self::assertArrayHasKey('no_change', $changed[0], 'Password was changed');
        self::assertEquals($settings_before, $settings_after, 'Config has changed.');

        // Set same server
        $settings_before = $this->getMailingSettings();
        $changed = Setting::setSmtpConfig()
            ->setServer(self::DEFAULT_SMTP_CONFIG['smtpServer'])
            ->setPort(self::DEFAULT_SMTP_CONFIG['smtpPort'])
            ->setUser(self::DEFAULT_SMTP_CONFIG['smtpUsername'])
            ->setPass(self::DEFAULT_SMTP_CONFIG['smtpPassword'])
            ->setNeedAuth(self::DEFAULT_SMTP_CONFIG['smtpAuth'])
            ->execute();
        $settings_after = $this->getMailingSettings();
        self::assertCount(1, $changed, 'Failed to set server');
        self::assertArrayHasKey('no_change', $changed[0], 'Config has changed.');
        self::assertEquals($settings_before, $settings_after, 'Config has changed.');
    }
}
