<?php

namespace Civi\RcBase\Utils;

use Civi\Api4\Activity;
use Civi\Api4\Contact;
use Civi\Api4\GroupContact;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\ApiWrapper\Remove;
use Civi\RcBase\ApiWrapper\Update;
use Civi\RcBase\Exception\DataBaseException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Settings;
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

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testPruneChangelog()
    {
        // Switch on detailed logging
        $old_logging = Settings::get('logging');
        Settings::save('logging', 1);

        // Prepare
        $contact_id = PHPUnit::createIndividual(0, ['first_name' => 'old name']);
        Update::contact($contact_id, ['first_name' => 'new name']);

        DB::pruneChangelog('civicrm_contact', 'id', $contact_id);
        self::assertCount(0, DB::query('SELECT * FROM log_civicrm_contact WHERE id = %1', [1 => [$contact_id, 'Positive']]), 'Changelog not pruned');

        // Restore logging setting
        Settings::save('logging', $old_logging);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testProcedureDeleteOrphans()
    {
        // Create orphaned records
        DB::query('ALTER TABLE civicrm_email DROP FOREIGN KEY FK_civicrm_email_contact_id');
        for ($i = 0; $i < 5; $i++) {
            $contact_id = PHPUnit::createIndividualWithEmail();
            Remove::entity('Contact', $contact_id);
        }
        DB::query('INSERT INTO civicrm_email (email, contact_id) VALUES ("multiple.email.same.contact@example.com", 9999)');
        DB::query('INSERT INTO civicrm_email (email, contact_id) VALUES ("multiple.email.same.contact@example.com", 9999)');
        // This should not be counted as orphan since contact_id is NULL (which may be allowed)
        DB::query('INSERT INTO civicrm_email (email, contact_id) VALUES ("contact_id.is.null@example.com", NULL)');

        // Try to add FK back - should fail
        try {
            DB::query('ALTER TABLE civicrm_email ADD CONSTRAINT FK_civicrm_email_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE');
        } catch (DataBaseException $ex) {
            self::assertStringContainsString('DB Error: constraint violation', $ex->getMessage(), 'Unexpected error message');
        }

        DB::query('CALL civicrm_delete_orphans("civicrm_email", "contact_id", "civicrm_contact", "id", @affected)');
        $result = DB::query('SELECT @affected');
        self::assertCount(1, $result, 'Wrong number of result rows');
        self::assertEquals(7, $result[0]['@affected'], 'Wrong number of affected rows');

        // Add back FK - should work now
        DB::query('ALTER TABLE civicrm_email ADD CONSTRAINT FK_civicrm_email_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE');

        DB::query('CALL civicrm_delete_orphans("civicrm_email", "contact_id", "civicrm_contact", "id", @affected)');
        $result = DB::query('SELECT @affected');
        self::assertCount(1, $result, 'Wrong number of result rows');
        self::assertEquals(0, $result[0]['@affected'], 'Wrong number of affected rows');
    }
}
