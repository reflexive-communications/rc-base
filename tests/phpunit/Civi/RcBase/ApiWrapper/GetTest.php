<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Utils\DB;
use Civi\RcBase\Utils\PHPUnit;
use CRM_Core_BAO_LocationType;
use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class GetTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntity()
    {
        $api_key = 'get_contact_api_key';
        $contact_id = PHPUnit::createIndividual(0, ['api_key' => $api_key,]);

        $results = Get::entity('Contact', ['where' => [['api_key', '=', $api_key]]]);
        self::assertSame('Contact', $results->entity, 'Wrong entity called');
        self::assertSame('get', $results->action, 'Wrong action called');
        self::assertCount(1, $results, 'Contact not found');
        self::assertArrayHasKey('id', $results[0], 'id not returned');
        self::assertSame($contact_id, $results[0]['id'], 'Wrong contact returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityInvalidEntityThrowsException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('API (NonExistent, get) does not exist');
        Get::entity('NonExistent');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testParseResultFirst()
    {
        $counter = PHPUnit::nextCounter();
        PHPUnit::createIndividual($counter);

        // Check existent record
        $results = Get::entity('Contact', ['where' => [['external_identifier', '=', "ext_{$counter}"]]]);
        self::assertGreaterThan(30, count(Get::parseResultsFirst($results)), 'Not all fields returned: For a contact entity at least 30 fields is expected');
        self::assertSame("ext_{$counter}", Get::parseResultsFirst($results, 'external_identifier'), 'external_identifier not returned as string');

        // Check non-existent record
        $results = Get::entity('Contact', ['where' => [['external_identifier', '=', 'non-existent']]]);
        self::assertNull(Get::parseResultsFirst($results), 'Not null returned on non-existent record');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testParseResultFirstNonExistentFieldThrowsException()
    {
        PHPUnit::createIndividual();
        $results = Get::entity('Contact');

        self::expectException(APIException::class);
        self::expectExceptionMessage('non_existent_field not found');
        Get::parseResultsFirst($results, 'non_existent_field');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityById()
    {
        $values = [
            'name' => 'test_entity_tag',
            'description' => 'This is a test tag',
        ];
        $id = Create::tag($values);

        $data = Get::entityByID('Tag', $id);
        self::assertArrayHasKey('name', $data, 'name missing');
        self::assertArrayHasKey('description', $data, 'description missing');
        self::assertSame($values['name'], $data['name'], 'Wrong name');
        self::assertSame($values['description'], $data['description'], 'Wrong description');
        // Check single field
        self::assertSame($values['description'], Get::entityByID('Tag', $id, 'description'), 'description not returned as string');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetEntityByName()
    {
        $values = [
            'title' => 'User friendly title',
            'name' => 'group_machine_name',
        ];
        $id = Create::entity('Group', $values);

        $data = Get::entityByName('Group', $values['name']);
        self::assertArrayHasKey('id', $data, 'id missing');
        self::assertArrayHasKey('title', $data, 'title missing');
        self::assertSame($id, $data['id'], 'Wrong id');
        self::assertSame($values['title'], $data['title'], 'Wrong title');
        // Check single field
        self::assertSame($values['title'], Get::entityByID('Group', $id, 'title'), 'title not returned as string');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testGetContactIdByEmail()
    {
        // Create contacts
        $contact_id_a = PHPUnit::createIndividual();
        $contact_id_b = PHPUnit::createIndividual();

        // Create emails
        $email_a = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $email_b = [
            'email' => 'ceasar@home.rome',
            'location_type_id' => 2,
        ];
        $email_c = [
            'email' => 'antonius@senate.rome',
            'location_type_id' => 1,
        ];
        Create::email($contact_id_a, $email_a);
        Create::email($contact_id_a, $email_b);
        Create::email($contact_id_b, $email_c);

        // Check valid email
        self::assertSame($contact_id_a, Get::contactIDByEmail($email_a['email']), 'Wrong contact ID returned');
        self::assertSame($contact_id_a, Get::contactIDByEmail($email_b['email']), 'Wrong contact ID returned');
        self::assertSame($contact_id_b, Get::contactIDByEmail($email_c['email']), 'Wrong contact ID returned');
        // Check empty email
        self::assertNull(Get::contactIDByEmail(''), 'Not null returned on empty email');
        // Check non-existent email
        self::assertNull(Get::contactIDByEmail('nonexistent@rome.com'), 'Not null returned on non-existent email');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetSystemUser()
    {
        self::assertSame(PHPUnit::createLoggedInUser(), Get::systemUserContactID(), 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testGetDefaultLocationType()
    {
        $def_loc_type = (int)CRM_Core_BAO_LocationType::getDefault()->id;

        self::assertSame($def_loc_type, Get::defaultLocationTypeID(), 'Wrong default location type ID returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testContactHasTag()
    {
        // Create contact and tag
        $contact_id_tagged = PHPUnit::createIndividual();
        $contact_id_untagged = PHPUnit::createIndividual();
        $tag_id = Create::tag(['name' => 'Test tag']);
        $entity_tag_id = Create::tagContact($contact_id_tagged, $tag_id);

        self::assertSame($entity_tag_id, Get::contactHasTag($contact_id_tagged, $tag_id), 'Wrong entity tag ID returned');
        self::assertNull(Get::contactHasTag($contact_id_untagged, $tag_id), 'Not null returned on non-tagged contact');
        self::assertNull(Get::contactHasTag($contact_id_tagged, DB::getNextAutoIncrementValue('civicrm_tag')), 'Not null returned on non-existent tag');
        self::assertNull(Get::contactHasTag(DB::getNextAutoIncrementValue('civicrm_contact'), $tag_id), 'Not null returned on non-existent contact ID');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid ID');
        Get::contactHasTag(-1, $tag_id);
    }
}
