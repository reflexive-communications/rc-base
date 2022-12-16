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

    public function testGetEntityInvalidEntityThrowsException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('API (NonExistent, get) does not exist');
        Get::entity('NonExistent');
    }
}
