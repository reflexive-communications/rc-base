<?php

/**
 * Test Utils Basic class
 *
 * @group headless
 */
class CRM_RcBase_Test_UtilsHeadlessTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\NotImplementedException
     */
    public function testGetNextAutoIncrementValue()
    {
        $results = civicrm_api4('Tag', 'create', [
            'values' => [
                'name' => 'test_tag',
            ],
        ]);
        $last_id = $results->first()['id'];

        self::assertSame($last_id + 1, CRM_RcBase_Test_UtilsHeadless::getNextAutoIncrementValue('civicrm_tag'), 'Bad value returned');
    }

    public function testMissingTableNameThrowsException()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing table name');
        CRM_RcBase_Test_UtilsHeadless::getNextAutoIncrementValue('');
    }

    public function testMissingSqlThrowsException()
    {
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Missing SQL query');
        CRM_RcBase_Test_UtilsHeadless::rawSqlQuery('');
    }

    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\NotImplementedException
     */
    public function testCvApi()
    {
        $results = civicrm_api4('Tag', 'create', [
            'values' => [
                'name' => 'another_test_tag',
            ],
        ]);
        $id_expected = $results->first()['id'];

        $id_cv = CRM_RcBase_Test_UtilsHeadless::cvApi4Get('Tag', ['id'], ['name=another_test_tag']);
        self::assertCount(1, $id_cv);
        self::assertSame($id_expected, $id_cv[0]['id'], 'Bad ID returned on get');

        $tag = [
            'name' => 'one_more_test_tag',
        ];
        $id_created = CRM_RcBase_Test_UtilsHeadless::cvApi4Create('Tag', $tag);
        self::assertSame($id_expected + 1, $id_created, 'Bad ID returned on create');
    }
}
