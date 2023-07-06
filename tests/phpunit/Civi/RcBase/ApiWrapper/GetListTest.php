<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class GetListTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testGetOptionValues()
    {
        // Prepare
        $activity_type = ['label' => 'new-activity-type'];
        $activity_type_id = Create::optionValue('activity_type', $activity_type);

        // Check default
        $activity_types = GetList::optionValues('activity_type');
        self::assertSame($activity_types[$activity_type_id], $activity_type['label'], 'Wrong activity type returned');

        // Add extra params (limit)
        $activity_types = GetList::optionValues('activity_type', ['limit' => 5]);
        self::assertCount(5, $activity_types, 'Wrong number of activity types returned');

        // Add extra params (where)
        $activity_types = GetList::optionValues('activity_type', ['where' => [['label', '=', $activity_type['label']]]]);
        self::assertCount(1, $activity_types, 'Not single activity type returned');

        // Check empty, invalid option group
        self::assertSame([], GetList::optionValues(''), 'Empty option group should return empty array');
        self::assertSame([], GetList::optionValues('non-existing-option-group'), 'Non-existing option group should return empty array');
    }
}
