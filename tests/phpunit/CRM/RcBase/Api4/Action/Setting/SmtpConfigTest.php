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
            'smtpAuth' => false,
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

        $smtp_configs = Setting::get()
            ->addSelect('mailing_backend')
            ->execute();
        self::assertCount(1, $smtp_configs, 'Failed to get configs');
        self::assertArrayHasKey('value', $smtp_configs[0], 'Failed to get configs');
        self::assertArrayHasKey('smtpServer', $smtp_configs[0]['value'], 'Missing config: smtpServer');
        self::assertEquals($new_configs['server'], $smtp_configs[0]['value']['smtpServer']);
        self::assertArrayHasKey('smtpPort', $smtp_configs[0]['value'], 'Missing config: smtpPort');
        self::assertEquals($new_configs['port'], $smtp_configs[0]['value']['smtpPort']);
        self::assertArrayHasKey('smtpAuth', $smtp_configs[0]['value'], 'Missing config: smtpAuth');
        self::assertEquals($new_configs['need_auth'], $smtp_configs[0]['value']['smtpAuth']);
        self::assertArrayHasKey('smtpUsername', $smtp_configs[0]['value'], 'Missing config: smtpUsername');
        self::assertEquals($new_configs['user'], $smtp_configs[0]['value']['smtpUsername']);
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
        self::assertEquals($new_pass, $pass[0]['pass'], 'Failed to change password');
    }
}
