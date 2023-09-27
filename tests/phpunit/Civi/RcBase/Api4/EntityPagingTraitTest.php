<?php

namespace Civi\RcBase\Api4;

// Need to include trait manually as autoload does not work (why?)
require_once 'Civi/RcBase/Api4/EntityPagingTrait.php';

use Civi\RcBase\ApiWrapper\Get;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Utils\PHPUnit;

/**
 * @group headless
 */
class EntityPagingTraitTest extends HeadlessTestCase
{
    use EntityPagingTrait;

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidBatchSizeThrowsException()
    {
        $this->batchSize = 0;
        $this->idOffset = 1;
        $this->maxProcessed = 1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('batch size');
        $this->validatePagingParams();
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidIdOffsetThrowsException()
    {
        $this->batchSize = 1;
        $this->idOffset = 0;
        $this->maxProcessed = 1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('ID offset');
        $this->validatePagingParams();
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidMaxProcessedThrowsException()
    {
        $this->batchSize = 1;
        $this->idOffset = 1;
        $this->maxProcessed = -1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('max processed');
        $this->validatePagingParams();
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testFetchNextPage()
    {
        // Simple query
        $contact_id = PHPUnit::createIndividual();
        $results = $this->fetchNextPage('civicrm_contact', ['id'], '', 1, $contact_id - 1);
        self::assertCount(1, $results, 'not single row returned');
        self::assertArrayHasKey('id', $results[0], 'id field not returned');
        self::assertEquals($contact_id, $results[0]['id'], 'wrong id returned');

        // Where clause test
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), ['contact_type' => 'Organization']);
        $results = $this->fetchNextPage('civicrm_contact', ['id'], 'contact_type != "Organization"', 1, $contact_id - 1);
        self::assertCount(0, $results, 'row returned when none expected');

        // Complex query
        $params = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'external_identifier' => 'abc-123',
        ];
        PHPUnit::createIndividual(PHPUnit::nextCounter(), $params);
        $results = $this->fetchNextPage(
            'civicrm_contact',
            ['external_identifier AS id', 'first_name AS name', 'last_name'],
            "external_identifier = '{$params['external_identifier']}'",
            1000,
            0
        );
        self::assertCount(1, $results, 'not single row returned');
        self::assertArrayHasKey('id', $results[0], 'external_id not as id returned');
        self::assertArrayHasKey('name', $results[0], 'first_name not as name returned');
        self::assertArrayHasKey('last_name', $results[0], 'last_name not returned');
        self::assertEquals($params['external_identifier'], $results[0]['id'], 'wrong id returned');
        self::assertEquals($params['first_name'], $results[0]['name'], 'wrong name returned');
        self::assertEquals($params['last_name'], $results[0]['last_name'], 'wrong last_name returned');

        // Get all contacts
        $contacts = Get::entity('Contact', [
            'where' => [
                ['contact_type', '=', 'Individual'],
                ['is_deceased', '=', false],
                ['is_deleted', '=', false],
            ],
        ]);
        $results = $this->fetchNextPage(
            'civicrm_contact',
            ['id'],
            'contact_type = "Individual" AND is_deceased = 0 AND is_deleted = 0',
            1000,
            0
        );
        self::assertCount(count($contacts), $results, 'wrong number of rows returned');

        // Empty select
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('select');
        $this->fetchNextPage('civicrm_contact', [], '', 1, 0);
    }
}
