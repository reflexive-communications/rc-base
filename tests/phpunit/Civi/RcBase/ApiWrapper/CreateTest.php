<?php

namespace Civi\RcBase\ApiWrapper;

use Civi\Api4\Contact;
use Civi\RcBase\Exception\APIException;
use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;
use Civi\RcBase\Utils\PHPUnit;

/**
 * @group headless
 */
class CreateTest extends HeadlessTestCase
{

    protected static array $customFields;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Option groups for checkbox, radio and select fields
        $option_group_id_checkbox = Create::entity('OptionGroup', ['name' => 'checkbox']);
        Create::optionValue('checkbox', ['label' => 'option1']);
        Create::optionValue('checkbox', ['label' => 'option2']);
        Create::optionValue('checkbox', ['label' => 'option3']);
        $option_group_id_radio = Create::entity('OptionGroup', ['name' => 'radio']);
        Create::optionValue('radio', ['label' => 'option_A']);
        Create::optionValue('radio', ['label' => 'option_B']);
        Create::optionValue('radio', ['label' => 'option_C']);
        $option_group_id_select = Create::entity('OptionGroup', ['name' => 'select']);
        Create::optionValue('select', ['label' => 'one']);
        Create::optionValue('select', ['label' => 'two']);
        Create::optionValue('select', ['label' => 'three']);

        // Custom fields
        $custom_group_id = Create::entity('CustomGroup', ['title' => 'contact_info', 'extends' => 'Contact']);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'string_text',
            'data_type' => 'String',
            'html_type' => 'Text',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'string_checkbox',
            'data_type' => 'String',
            'html_type' => 'CheckBox',
            'option_group_id' => $option_group_id_checkbox,
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'string_radio',
            'data_type' => 'String',
            'html_type' => 'Radio',
            'option_group_id' => $option_group_id_radio,
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'string_select',
            'data_type' => 'String',
            'html_type' => 'Select',
            'option_group_id' => $option_group_id_select,
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'integer_text',
            'data_type' => 'Int',
            'html_type' => 'Text',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'yes_no',
            'data_type' => 'Boolean',
            'html_type' => 'Radio',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'date_yyyy-mm-dd',
            'data_type' => 'Date',
            'html_type' => 'Select Date',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'timestamp',
            'data_type' => 'Date',
            'html_type' => 'Select Date',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'link',
            'data_type' => 'Link',
            'html_type' => 'Link',
        ]);
        Create::entity('CustomField', [
            'custom_group_id' => $custom_group_id,
            'label' => 'note_textarea',
            'data_type' => 'Memo',
            'html_type' => 'TextArea',
        ]);
        self::$customFields = [
            'string_text' => 'contact_info.string_text',
            'string_checkbox' => 'contact_info.string_checkbox',
            'string_radio' => 'contact_info.string_radio',
            'string_select' => 'contact_info.string_select',
            'integer_text' => 'contact_info.integer_text',
            'yes_no' => 'contact_info.yes_no',
            'date_yyyy-mm-dd' => 'contact_info.date_yyyy-mm-dd',
            'timestamp' => 'contact_info.timestamp',
            'link' => 'contact_info.link',
            'note_textarea' => 'contact_info.note_textarea',
        ];
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateEntity()
    {
        $counter = PHPUnit::nextCounter().time();
        $values = [
            'contact_type' => 'Individual',
            'first_name' => 'user_'.$counter,
            self::$customFields['string_text'] => 'Some text',
            self::$customFields['string_checkbox'] => ['option1', 'option3'],
            self::$customFields['string_radio'] => 'option_B',
            self::$customFields['string_select'] => 'two',
            self::$customFields['integer_text'] => 123,
            self::$customFields['yes_no'] => true,
            self::$customFields['date_yyyy-mm-dd'] => '2021-03-15',
            self::$customFields['timestamp'] => '2021-03-15 12:34:56',
            self::$customFields['link'] => 'https://example.com',
            self::$customFields['note_textarea'] => "multi\nline\nnote",
        ];

        // Check contact not exists beforehand
        $result = Get::entitySingle('Contact', [
            'select' => ['*', 'custom.*'],
            'where' => [['first_name', '=', $values['first_name']]],
        ]);
        self::assertNull($result, 'Contact should not be present');

        $contact_id = Create::entity('Contact', $values);

        // Check contact is present now and check ID also
        $result = Get::entitySingle('Contact', [
            'select' => ['*', 'custom.*'],
            'where' => [['first_name', '=', $values['first_name']]],
        ]);
        self::assertGreaterThan(0, count($result), 'Failed to locate contact');
        self::assertSame($result['id'], $contact_id, 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testMissingRequiredFieldThrowsApiException()
    {
        self::expectException(APIException::class);
        self::expectExceptionMessage('Mandatory values missing');
        Create::entity('Activity', [
            'subject' => 'test subject',
        ]);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateContactWithExtraUnknownFields()
    {
        $counter = PHPUnit::nextCounter();
        $values = [
            'contact_type' => 'Individual',
            'first_name' => 'user_'.$counter,
            'nonexistent_field_string' => 'Ides of March',
            'nonexistent_field_int' => 15,
            'nonexistent_field_bool' => true,
        ];

        $contact_id = Create::entity('Contact', $values);

        $contacts = Contact::get()
            ->addSelect('id')
            ->addWhere('first_name', '=', $values['first_name'])
            ->execute();
        self::assertCount(1, $contacts, 'Failed to locate contact');
        self::assertArrayHasKey('id', $contacts->first(), 'Id not returned');
        self::assertSame($contacts->first()['id'], $contact_id, 'Wrong contact ID returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateContactWithDuplicateExternalIdThrowsException()
    {
        $counter = PHPUnit::nextCounter();
        $values = [
            'contact_type' => 'Individual',
            'external_identifier' => 'ext_'.$counter,
        ];
        Create::entity('Contact', $values);

        // Create same contact
        self::expectException(APIException::class);
        self::expectExceptionMessage('DB Error: already exists');
        Create::entity('Contact', $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateEmail()
    {
        $values = [
            'email' => 'ceasar@senate.rome',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::email($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::email(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreatePhone()
    {
        $values = [
            'phone' => '+12343243',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::phone($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::phone(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateAddress()
    {
        $values = [
            'city' => 'Rome',
            'location_type_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::address($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::address(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateRelationship()
    {
        $contact_id_other = PHPUnit::createIndividual();
        $values = [
            'contact_id_b' => $contact_id_other,
            'relationship_type_id' => 1,
            'description' => 'Test',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::relationship($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::relationship(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateContribution()
    {
        $values = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => '12345',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::contribution($contact_id, $values), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::contribution(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateContributionWithDuplicateTransactionIdThrowsException()
    {
        $values = [
            'financial_type_id' => 1,
            'total_amount' => 13.43,
            'trxn_id' => 'duplicate',
        ];
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::contribution($contact_id, $values), 'Valid ID needs to be returned');

        // Create same contribution
        self::expectException(APIException::class);
        self::expectExceptionMessage('Duplicate error');
        Create::contribution($contact_id, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testCreateActivity()
    {
        $contact_id = PHPUnit::createIndividual();
        $contact_id_source = PHPUnit::createIndividual();

        // Source contact passed
        $values = [
            'source_contact_id' => $contact_id_source,
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];
        $activity_id = Create::activity($contact_id, $values);
        self::assertNotNull($activity_id, 'Valid ID needs to be returned');
        $activity_contact = Get::entitySingle('ActivityContact', [
            'where' => [
                ['activity_id', '=', $activity_id],
                ['record_type_id:name', '=', 'Activity Source'],
            ],
            'limit' => 1,
        ]);
        self::assertSame($contact_id_source, $activity_contact['contact_id'], 'Wrong source contact');

        // Source contact not passed
        $values = [
            'activity_type_id' => 1,
            'subject' => 'Tribute',
        ];
        $activity_id = Create::activity($contact_id, $values);
        self::assertNotNull($activity_id, 'Valid ID needs to be returned');
        $activity_contact = Get::entitySingle('ActivityContact', [
            'where' => [
                ['activity_id', '=', $activity_id],
                ['record_type_id:name', '=', 'Activity Source'],
            ],
            'limit' => 1,
        ]);
        self::assertSame(Get::systemUserContactID(), $activity_contact['contact_id'], 'Wrong source contact');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('contact ID');
        Create::activity(0, $values);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testTagContact()
    {
        $values = [
            'name' => 'Test tag',
        ];
        $tag_id = Create::entity('Tag', $values);
        $contact_id = PHPUnit::createIndividual();

        self::assertNotNull(Create::tagContact($contact_id, $tag_id), 'Valid ID needs to be returned');

        // Check invalid ID
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('tag ID');
        Create::tagContact($contact_id, 0);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateGroup()
    {
        $values = [
            'title' => 'Placeholder group',
            'name' => 'place_holder_group',
            'description' => 'This is some description',
        ];
        self::assertNotNull(Create::group($values), 'Valid ID needs to be returned');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\APIException
     */
    public function testCreateTag()
    {
        $values = [
            'name' => 'test_tag',
            'description' => 'This is a test tag',
            'is_reserved' => true,
            'is_selectable' => false,
        ];
        self::assertNotNull(Create::tag($values), 'Valid ID needs to be returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testCreateOptionValue()
    {
        $values = [
            'label' => 'new_status',
            'name' => 'new_status',
            'value' => 'new_state',
        ];
        self::assertNotNull(Create::optionValue('contribution_status', $values), 'Valid ID needs to be returned');

        // Check empty option group
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('option group name');
        Create::optionValue('', $values);
    }
}
