<?php

namespace Civi\RcBase\Utils;

use Civi\Api4\Activity;
use Civi\Api4\Contact;
use Civi\Api4\GroupContact;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\Exception\DataBaseException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;
use CRM_Contact_BAO_Contact;

/**
 * @group headless
 */
class DBTest extends HeadlessTestCase
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
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('table name');
        DB::getNextAutoIncrementValue('');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testGetNextAutoIncrementValueInvalidTableThrowsException()
    {
        self::expectException(DataBaseException::class);
        self::expectExceptionMessage('Failed to get next auto increment value for table');
        DB::getNextAutoIncrementValue('invalid_table');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testQuery()
    {
        $middle_name = 'db query middle name test';
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), [
            'middle_name' => $middle_name,
        ]);

        $records = DB::query('SELECT id FROM civicrm_contact WHERE middle_name = %1', [1 => [$middle_name, 'String']]);
        self::assertCount(1, $records, 'Failed to retrieve contact');
        self::assertArrayHasKey('id', $records[0], 'ID field missing');
        self::assertEquals($contact_id, $records[0]['id'], 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testAddRemoveContactToGroup()
    {
        $contact_id = PHPUnit::createIndividual();
        $group_id = Create::group(['title' => 'test group']);

        $group_members = function () use ($contact_id, $group_id) {
            $results = GroupContact::get()
                ->addSelect('id', 'status')
                ->addWhere('contact_id', '=', $contact_id)
                ->addWhere('group_id', '=', $group_id)
                ->execute();

            return $results->getArrayCopy();
        };

        // Test adding (twice)
        DB::addContactToGroup($contact_id, $group_id);
        self::assertCount(1, $group_members(), 'Record missing');
        self::assertSame('Added', $group_members()[0]['status'], 'Contact not added to group');
        DB::addContactToGroup($contact_id, $group_id);
        self::assertCount(1, $group_members(), 'Contact added twice to group');
        self::assertSame('Added', $group_members()[0]['status'], 'Contact not added to group');
        // Test removing (twice)
        DB::removeContactFromGroup($contact_id, $group_id);
        self::assertCount(1, $group_members(), 'Records missing');
        self::assertSame('Removed', $group_members()[0]['status'], 'Contact not removed from group');
        DB::removeContactFromGroup($contact_id, $group_id);
        self::assertCount(1, $group_members(), 'Records missing');
        self::assertSame('Removed', $group_members()[0]['status'], 'Contact not removed from group');
        // Test re-adding
        DB::addContactToGroup($contact_id, $group_id);
        self::assertCount(1, $group_members(), 'Records missing');
        self::assertSame('Added', $group_members()[0]['status'], 'Contact not re-added to group');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testNormalizeValue()
    {
        $contact_type_a = 'typeA';
        $contact_type_b = 'typeB';
        Create::entity('ContactType', [
            'name' => $contact_type_a,
            'label' => $contact_type_a,
            'parent_id:name' => 'Individual',
        ]);
        Create::entity('ContactType', [
            'name' => $contact_type_b,
            'label' => $contact_type_b,
            'parent_id:name' => 'Individual',
        ]);

        $values = [
            'contact_type' => 'Individual',
            'contact_sub_type' => [$contact_type_a, $contact_type_b],
            'nick_name' => 'Eazy-E',
            'do_not_email' => true,
            'gender_id' => 2,
        ];
        $contact_id = Create::contact($values);

        $dao = new CRM_Contact_BAO_Contact();
        $dao->id = $contact_id;
        $dao->find();

        $result = DB::normalizeValues($dao);
        self::assertCount(1, $result, 'Contact not found');
        self::assertSame($values['contact_sub_type'], $result[0]['contact_sub_type'], 'Wrong data returned');
        self::assertSame($values['nick_name'], $result[0]['nick_name'], 'Wrong data returned');
        self::assertSame($values['do_not_email'], $result[0]['do_not_email'], 'Wrong data returned');
        self::assertSame($values['gender_id'], $result[0]['gender_id'], 'Wrong data returned');
        self::assertNull($result[0]['external_identifier'], 'Wrong data returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testCheckIfTableExists()
    {
        self::assertTrue(DB::checkIfTableExists('civicrm_contact'), 'Existing table not found');
        self::assertTrue(DB::checkIfTableExists('civicrm_%'), 'Wildcard table not found');
        self::assertFalse(DB::checkIfTableExists('non_existent_table'), 'Non-existent table found');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     */
    public function testDeleteRecord()
    {
        $contact_id = PHPUnit::createIndividual();
        DB::deleteRecord('civicrm_contact', 'id', $contact_id);
        self::assertNull(Get::entityByID('Contact', $contact_id), 'Record not deleted');
    }
}
