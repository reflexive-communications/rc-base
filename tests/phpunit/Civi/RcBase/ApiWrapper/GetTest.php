<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Utils\PHPUnit;
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
}
