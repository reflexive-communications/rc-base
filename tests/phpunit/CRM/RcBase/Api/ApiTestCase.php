<?php

use Civi\Test\Api3DocTrait;
use Civi\Test\ContactTestTrait;
use Civi\Test\DbTestTrait;
use Civi\Test\GenericAssertionsTrait;
use Civi\Test\MailingTestTrait;

/**
 * Base test class for API headless tests
 * Contains helper functions for testing
 */
class CRM_RcBase_Api_ApiTestCase extends CRM_RcBase_HeadlessTestCase
{
    use Api3DocTrait;
    use GenericAssertionsTrait;
    use DbTestTrait;
    use ContactTestTrait;
    use MailingTestTrait;

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
        // Assemble Contact data
        $contact = $this->sampleContact('Individual', self::getNextContactSequence());
        $contact['external_identifier'] = self::getNextExternalID();
        // Remove unnecessary fields
        unset($contact['prefix_id']);
        unset($contact['suffix_id']);
        // Remove email
        unset($contact['email']);

        return $contact;
    }
}
