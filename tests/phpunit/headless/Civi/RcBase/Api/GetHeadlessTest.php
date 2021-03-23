<?php

use Civi\API\Exception\UnauthorizedException;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test API Get class
 *
 * @group headless
 */
class CRM_RcBase_Api_GetHeadlessTest extends TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
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
        return \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Create a clean DB before running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Set up a clean DB
        \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * @throws UnauthorizedException|API_Exception
     */
    public function testContactIdFromEmailWithExistentEmail()
    {
        $first_name = "John";
        $last_name = "Rambo";
        $email_address = "john.rambo@example.com";

        // Create user & add email
        $user = cv(
            'api Contact.create contact_type="Individual" first_name="'.$first_name.'" last_name="'.$last_name.'"'
        );
        $id = $user['id'];
        cv('api Email.create contact_id="'.$id.'" email="'.$email_address.'"');

        $this->assertSame($id, Civi\RcBase\Api\Get::contactIDFromEmail($email_address), 'Bad contact ID returned');
    }

    /**
     * @throws UnauthorizedException|API_Exception
     */
    public function testContactIdFromEmailWithNonExistentEmail()
    {
        $email_address = "nonexistent@example.com";

        $this->assertTrue(
            is_null(Civi\RcBase\Api\Get::contactIDFromEmail($email_address)),
            'Not null returned on non-existent ID'
        );
    }
}
