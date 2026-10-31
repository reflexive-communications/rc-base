<?php

namespace Civi\RcBase;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for contact defaults and hook delegation.
 */
class ContactDefaultsTest extends TestCase
{
    /**
     * Load the extension hook functions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        $extension_path = dirname(__DIR__, 4);
        if (!class_exists(ContactDefaults::class)) {
            require_once $extension_path.'/Civi/RcBase/ContactDefaults.php';
        }
        if (!function_exists('rc_base_civicrm_pre')) {
            require_once $extension_path.'/rc_base.php';
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideContactTypes(): array
    {
        return [
            'household' => ['Household'],
            'individual' => ['Individual'],
            'organization' => ['Organization'],
        ];
    }

    /**
     * @dataProvider provideContactTypes
     */
    public function testDefaultNoBulkValueIsAppliedToNewContacts(string $contact_type): void
    {
        $params = ['display_name' => 'A new contact'];

        \rc_base_civicrm_pre('create', $contact_type, null, $params);

        self::assertSame([
            'display_name' => 'A new contact',
            'is_opt_out' => true,
        ], $params);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function provideExplicitNoBulkValues(): array
    {
        return [
            'false' => [false],
            'true' => [true],
            'integer zero' => [0],
            'integer one' => [1],
            'null' => [null],
        ];
    }

    /**
     * @dataProvider provideExplicitNoBulkValues
     *
     * @param mixed $is_opt_out
     */
    public function testExplicitNoBulkValueIsPreserved($is_opt_out): void
    {
        $params = ['is_opt_out' => $is_opt_out];

        \rc_base_civicrm_pre('create', 'Individual', null, $params);

        self::assertSame(['is_opt_out' => $is_opt_out], $params);
    }

    /**
     * @return void
     */
    public function testNonCreateOperationIsUnchanged(): void
    {
        $params = [];

        \rc_base_civicrm_pre('edit', 'Individual', 1, $params);

        self::assertSame([], $params);
    }

    /**
     * @return void
     */
    public function testNonContactObjectIsUnchanged(): void
    {
        $params = [];

        \rc_base_civicrm_pre('create', 'Activity', null, $params);

        self::assertSame([], $params);
    }
}