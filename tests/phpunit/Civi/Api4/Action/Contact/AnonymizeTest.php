<?php

namespace Civi\Api4\Action\Contact;

use Civi\Api4\Address;
use Civi\Api4\Contact;
use Civi\Api4\Email;
use Civi\Api4\IM;
use Civi\Api4\Phone;
use Civi\Api4\Website;
use Civi\RcBase\Utils\PHPUnit;
use CRM_RcBase_HeadlessTestCase;

/**
 * @group headless
 */
class AnonymizeTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testAnonymize()
    {
        // Create contact to anonymize
        $values = [
            // Names
            'first_name' => 'John',
            'middle_name' => 'M.',
            'last_name' => 'Doe',
            'prefix_id' => 3,
            'suffix_id' => 2,
            'formal_title' => 'Prof.',
            'nick_name' => 'Johnny Boy',
            // IDs
            'external_identifier' => 123,
            'legal_identifier' => 'legal ID',
            'api_key' => 'somekey',
            'user_unique_id' => 22,
            // Greetings
            'email_greeting_custom' => 'Dear Johnny!',
            'email_greeting_display' => 'Dear John!',
            'postal_greeting_custom' => 'Dear Johnny!',
            'postal_greeting_display' => 'Dear John!',
            'addressee_custom' => 'Dear Johnny!',
            'addressee_display' => 'Dear John!',
            // Privacy fields
            'do_not_email' => false,
            'do_not_phone' => false,
            'do_not_mail' => false,
            'do_not_sms' => false,
            'do_not_trade' => false,
            // Other
            'contact_sub_type' => null,
            'image_URL' => 'https://my.image.com/profile.png',
            'source' => 'testing',
            'gender_id' => 1,
            'birth_date' => '1920-01-01',
            'deceased_date' => '2020-12-31',
            'job_title' => 'Advisor',
            'employer_id' => 1,
        ];
        $contact_id = PHPUnit::createIndividual(PHPUnit::nextCounter(), $values);
        \Civi\RcBase\ApiWrapper\Create::email($contact_id, ['email' => 'john@example.com', 'location_type_id:name' => 'Home']);
        \Civi\RcBase\ApiWrapper\Create::email($contact_id, ['email' => 'john@company.com', 'location_type_id:name' => 'Work']);
        \Civi\RcBase\ApiWrapper\Create::address($contact_id, ['supplemental_address_1' => 'home address', 'location_type_id:name' => 'Home']);
        \Civi\RcBase\ApiWrapper\Create::address($contact_id, ['supplemental_address_1' => 'work address', 'location_type_id:name' => 'Work']);
        \Civi\RcBase\ApiWrapper\Create::phone($contact_id, ['phone' => '555316', 'location_type_id:name' => 'Home']);
        \Civi\RcBase\ApiWrapper\Create::phone($contact_id, ['phone' => '12222', 'location_type_id:name' => 'Work']);
        \Civi\RcBase\ApiWrapper\Create::entity('IM', ['contact_id' => $contact_id, 'name' => 'johnny']);
        \Civi\RcBase\ApiWrapper\Create::entity('Website', ['contact_id' => $contact_id, 'url' => 'https://example.org']);

        $contact_anonymized = Contact::anonymize()
            ->setContactID($contact_id)
            ->execute();
        self::assertCount(1, $contact_anonymized, 'Contact not returned');

        // Check correspondence
        foreach (['Email', 'Phone', 'Address', 'IM', 'Website'] as $entity) {
            $results = civicrm_api4($entity, 'get', ['where' => [['contact_id', '=', $contact_id]]]);
            self::assertCount(0, $results, "{$entity} not deleted");
        }

        // Check contact fields
        $contact_check = Contact::get()
            ->addWhere('id', '=', $contact_id)
            ->execute();
        self::assertCount(1, $contact_check, 'Contact not found');
        self::assertSame($contact_check[0], $contact_anonymized[0], 'Different contact returned');

        $fields = [
            // Names
            'first_name',
            'middle_name',
            'last_name',
            'prefix_id',
            'suffix_id',
            'formal_title',
            'nick_name',
            'sort_name',
            'display_name',
            // IDs
            'external_identifier',
            'legal_identifier',
            'api_key',
            'user_unique_id',
            // Greetings
            'communication_style_id',
            'email_greeting_id',
            'email_greeting_custom',
            'email_greeting_display',
            'postal_greeting_id',
            'postal_greeting_custom',
            'postal_greeting_display',
            'addressee_id',
            'addressee_custom',
            'addressee_display',
            // Other
            'contact_sub_type',
            'image_URL',
            'source',
            'gender_id',
            'birth_date',
            'deceased_date',
            'job_title',
            'employer_id',
        ];
        foreach ($fields as $field) {
            self::assertArrayHasKey($field, $contact_check[0], "{$field} missing");
            self::assertNull($contact_check[0][$field], "{$field} not deleted");
        }
        $fields = [
            // Privacy fields
            'do_not_email',
            'do_not_phone',
            'do_not_mail',
            'do_not_sms',
            'do_not_trade',
        ];
        foreach ($fields as $field) {
            self::assertArrayHasKey($field, $contact_check[0], "{$field} missing");
            self::assertTrue($contact_check[0][$field], "{$field} not set");
        }

        // Check non-existent
        $contact_nonexistent = Contact::anonymize()
            ->setContactID($contact_id + 1)
            ->execute();
        self::assertCount(0, $contact_nonexistent, 'Non-existent contact anonymized');
    }
}
