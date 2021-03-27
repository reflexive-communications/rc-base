<?php

use Civi\Test;
use Civi\Test\Api3DocTrait;
use Civi\Test\ContactTestTrait;
use Civi\Test\DbTestTrait;
use Civi\Test\GenericAssertionsTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\MailingTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base test class for headless tests
 * Contains helper functions for testing
 */
class CRM_RcBase_Test_BaseHeadlessTestCase extends TestCase implements HeadlessInterface
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
     * The setupHeadless function runs at the start of each test case, right before
     * the headless environment reboots.
     *
     * It should perform any necessary steps required for putting the database
     * in a consistent baseline -- such as loading schema and extensions.
     *
     * The utility `\Civi\Test::headless()` provides a number of helper functions
     * for managing this setup, and it includes optimizations to avoid redundant
     * setup work.
     *
     * @see \Civi\Test
     */
    public function setUpHeadless()
    {
        return Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB
        Test::headless()
            ->installMe(__DIR__)
            ->apply(true);
    }

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
}
