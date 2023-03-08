<?php

/**
 * Base test class for API headless tests
 * Contains helper functions for testing
 */
class CRM_RcBase_Api_ApiTestCase extends CRM_RcBase_HeadlessTestCase
{
    /**
     * External ID counter
     *
     * @var int
     */
    protected static $externalID = 0;

    /**
     * Contact sequence counter
     *
     * @var int
     */
    protected static $contactSequence = 0;

    /**
     * Get next ID in sequence (auto-increment)
     *
     * @return string Next ID
     */
    protected static function getNextExternalID(): string
    {
        self::$externalID++;

        return (string)self::$externalID;
    }

    /**
     * Get next contact sequence number (auto-increment)
     *
     * @return int Next ID
     */
    protected static function getNextContactSequence(): int
    {
        self::$contactSequence++;

        return self::$contactSequence;
    }

    /**
     * Sample contact parameters, next in the sequence
     *
     * @return array Contact parameters
     */
    protected function nextSampleIndividual(): array
    {
        return [
            'contact_type' => 'Individual',
            'first_name' => 'user'.self::getNextContactSequence(),
            'middle_name' => 'middle',
            'last_name' => 'Test',
            'external_identifier' => self::getNextExternalID(),
        ];
    }

    /**
     * Create contact
     *
     * @param array $extra_params Extra parameters to the Contact entity
     *
     * @return int Contact ID
     * @throws \CRM_Core_Exception
     */
    protected function individualCreate(array $extra_params = []): int
    {
        $params_def = $this->nextSampleIndividual();

        return CRM_RcBase_Test_Utils::cvApi4Create('Contact', array_merge($params_def, $extra_params));
    }
}
