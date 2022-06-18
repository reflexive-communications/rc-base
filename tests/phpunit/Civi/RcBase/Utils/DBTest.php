<?php

namespace Civi\RcBase\Utils;

use Civi\Api4\Activity;
use Civi\Api4\Contact;
use CRM_Core_Exception;
use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class DBTest extends CRM_RcBase_HeadlessTestCase
{

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testGetNextAutoIncrementValue()
    {
        // civicrm_contact table
        $contact = Contact::create()
            ->addValue('contact_type', 'Individual')
            ->addValue('first_name', 'auto-increment')
            ->execute();
        $contact_id = (int)$contact->first()['id'];
        $next_id = DB::getNextAutoIncrementValue('civicrm_contact');
        self::assertSame($contact_id + 1, $next_id, 'Wrong auto increment value returned for: civicrm_contact');

        // civicrm_activity table
        $activity = Activity::create()
            ->addValue('activity_type_id:name', 'Meeting')
            ->addValue('source_contact_id', $contact_id)
            ->execute();
        $activity_id = (int)$activity->first()['id'];
        $next_id = DB::getNextAutoIncrementValue('civicrm_activity');
        self::assertSame($activity_id + 1, $next_id, 'Wrong auto increment value returned for: civicrm_activity');

        // Empty table
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing table name');
        DB::getNextAutoIncrementValue('');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testGetNextAutoIncrementValueInvalidTableThrowsException()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Failed to get next auto increment value for table');
        DB::getNextAutoIncrementValue('invalid_table');
    }
}
