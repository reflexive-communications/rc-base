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
            'smtpAuth' => '1',
            'smtpUsername' => 'admin',
            'smtpPassword' => 'pass',
            'smtpPasswordfdsf' => 'pass',
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
        self::assertArrayHasKey('is_error', $smtp_configs[0], "Missing is_error");
        self::assertArrayHasKey('error_message', $smtp_configs[0], "Missing is_error");
        self::assertEquals(true, $smtp_configs[0]['is_error']);
        self::assertEquals('Not allowed config: invalid_config', $smtp_configs[0]['error_message']);
    }
}
