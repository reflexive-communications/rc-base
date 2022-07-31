<?php

/**
 * @deprecated Use CRM_RcBase_Setting instead
 */
abstract class CRM_RcBase_Config
{
    private $configName;

    private $configuration;

    /**
     * CRM_RcBase_Config constructor.
     *
     * @param string $extensionName prefix for db.
     */
    public function __construct(string $extensionName)
    {
        $this->configName = $extensionName.'_config';
        $this->configuration = $this->defaultConfiguration();
    }

    /**
     * Provides a default configuration object.
     *
     * @return array the default configuration object.
     */
    abstract public function defaultConfiguration(): array;

    /**
     * Creates configuration table in db.
     *
     * @return bool the status of the db write process.
     */
    public function create(): bool
    {
        Civi::settings()->add([$this->configName => $this->configuration]);
        // check the save process with loading the saved content and compare
        // it with the current configuration
        $saved = Civi::settings()->get($this->configName);
        return $saved === $this->configuration;
    }

    /**
     * Removes configuration table from db.
     *
     * @return bool the status of the deletion process.
     */
    public function remove(): bool
    {
        Civi::settings()->revert($this->configName);
        // check the deletion process with loading the saved content and compare
        // it with null.
        $saved = Civi::settings()->get($this->configName);
        if (is_null($saved)) {
            // cleanup the class config
            $this->configuration = null;
            return true;
        }
        return false;
    }

    /**
     * Loads configuration from db.
     *
     * @throws CRM_Core_Exception.
     */
    public function load(): void
    {
        $conf = Civi::settings()->get($this->configName);
        // if not loaded well, it throws exception.
        if (is_null($conf) || !is_array($conf)) {
            throw new CRM_Core_Exception($this->configName.' config invalid.');
        }
        $this->configuration = $conf;
    }

    /**
     * Updates the configuration in db.
     *
     * @param array $newConfig the updated config to save
     *
     * @return bool the status of the update process.
     */
    public function update(array $newConfig): bool
    {
        Civi::settings()->set($this->configName, $newConfig);
        // check the save process with loading the saved content and compare
        // it with the newConfig configuration
        $saved = Civi::settings()->get($this->configName);
        if ($saved === $newConfig) {
            $this->configuration = $newConfig;
            return true;
        }
        return false;
    }

    /**
     * Returns the configuration.
     *
     * @return array the configuration.
     *
     * @throws CRM_Core_Exception.
     */
    public function get(): array
    {
        if (is_null($this->configuration)) {
            throw new CRM_Core_Exception($this->configName.' config is missing.');
        }
        return $this->configuration;
    }
}
