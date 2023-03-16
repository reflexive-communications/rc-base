<?php

use Civi\RcBase\HeadlessTestCase;

const DEFAULT_CONFIGURATION = [
    'Key1' => 'value1',
    'Key2' => 12,
    'Key3' => true,
    'Key4' => [],
    'Key5' => ['SubKey' => 'Great success!'],
];
const CONFIG_NAME = 'rcBase_test';

class TestConfig extends CRM_RcBase_Config
{
    public function defaultConfiguration(): array
    {
        return DEFAULT_CONFIGURATION;
    }
}

/**
 * @group headless
 */
class CRM_RcBase_ConfigTest extends HeadlessTestCase
{
    private function getConfig()
    {
        return new TestConfig(CONFIG_NAME);
    }

    /**
     * It checks that the create function works well.
     */
    public function testCreate()
    {
        $config = $this->getConfig();
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        self::assertSame(DEFAULT_CONFIGURATION, $cfg, 'Invalid configuration has been returned.');
        self::assertTrue($config->create(), 'Create config has to be successful multiple times.');
    }

    public function testCreateAfterChanges()
    {
        $config = $this->getConfig();
        self::assertTrue($config->create(), 'Create config has to be successful.');
        $cfg = $config->get();
        self::assertSame(DEFAULT_CONFIGURATION, $cfg, 'Invalid configuration has been returned.');
        self::assertTrue($config->create(), 'Create config has to be successful multiple times.');
        // Update config and call create. The updated config has to be created.
        foreach ($cfg as $k => $v) {
            $newKey = $k.'new';
            unset($cfg[$k]);
            $cfg[$newKey] = $v;
            $cfg[$newKey.'v2'] = false;
            break;
        }
        self::assertTrue($config->update($cfg), 'Update config has to be successful.');
        self::assertEmpty($config->load(), 'Load result supposed to be empty.');
        self::assertTrue($config->create(), 'Create config has to be successful with changed config.');
        $cfgNew = $config->get();
        self::assertSame($cfg, $cfgNew, 'Invalid configuration has been returned.');
        // reset the changes with creating the db with a new config instance.
        $otherConfig = $this->getConfig();
        self::assertTrue($otherConfig->create(), 'Create config has to be successful.');
        $otherCfg = $otherConfig->get();
        self::assertSame(DEFAULT_CONFIGURATION, $otherCfg, 'Invalid configuration has been returned.');
    }

    /**
     * It checks that the remove function works well.
     */
    public function testRemove()
    {
        $config = $this->getConfig();
        // preset the config.
        Civi::settings()->add([CONFIG_NAME => DEFAULT_CONFIGURATION]);
        self::assertTrue($config->remove(), 'Remove config has to be successful.');
    }

    /**
     * It checks that the get function works well.
     */
    public function testGet()
    {
        $config = $this->getConfig();
        // preset the config.
        Civi::settings()->add([CONFIG_NAME => DEFAULT_CONFIGURATION]);
        self::assertSame(DEFAULT_CONFIGURATION, $config->get(), 'Invalid configuration has been returned.');

        // remove the config
        self::assertTrue($config->remove(), 'Remove config has to be successful.');
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage(CONFIG_NAME.'_config config is missing.');
        $config->get();
    }

    /**
     * It checks that the update function works well.
     */
    public function testUpdate()
    {
        $config = $this->getConfig();
        // preset the config.
        Civi::settings()->add([CONFIG_NAME => DEFAULT_CONFIGURATION]);
        $cfg = Civi::settings()->get(CONFIG_NAME);
        foreach ($cfg as $k => $v) {
            $newKey = $k.'new';
            unset($cfg[$k]);
            $cfg[$newKey] = $v;
            $cfg[$newKey.'v2'] = false;
            break;
        }
        $cfg['brand-new-key'] = false;
        self::assertTrue($config->update($cfg), 'Update config has to be successful.');
        self::assertSame($cfg, $config->get(), 'Invalid updated configuration.');
    }

    public function testLoadCreatedConfig()
    {
        $config = $this->getConfig();
        // preset the config.
        $config->create();
        self::assertEmpty($config->load(), 'Load result supposed to be empty.');
        $cfg = $config->get();
        self::assertEquals(DEFAULT_CONFIGURATION, $cfg, 'Invalid loaded configuration.');
        // update the config
        $cfg['brand-new-key'] = false;
        self::assertTrue($config->update($cfg), 'Update config has to be successful.');
        self::assertEmpty($config->load(), 'Load result supposed to be empty.');
        self::assertSame($cfg, $config->get(), 'Invalid loaded configuration.');
    }
}
