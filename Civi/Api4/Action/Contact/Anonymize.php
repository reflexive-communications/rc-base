<?php

namespace Civi\Api4\Action\Contact;

use Civi;
use Civi\Api4\Contact;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\RcBase\ApiWrapper\Get;

/**
 * Anonymize Contact
 * Contact's all personal data will be deleted: name, addresses, correspondence details
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Anonymize extends AbstractAction
{
    /**
     * Contact to anonymize
     *
     * @var int
     * @required
     */
    protected int $contactID = 0;

    /**
     * @param \Civi\Api4\Generic\Result $result
     *
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function _run(Result $result): void
    {
        $results = Contact::get(false)
            ->addWhere('id', '=', $this->contactID)
            ->execute();
        if (count($results) < 1) {
            return;
        }

        // Delete correspondence
        foreach (['Email', 'Phone', 'Address', 'IM', 'Website'] as $entity) {
            $results = civicrm_api4($entity, 'get', ['where' => [['contact_id', '=', $this->contactID]], 'checkPermissions' => false]);
            if (count($results) > 0) {
                civicrm_api4($entity, 'delete', ['where' => [['contact_id', '=', $this->contactID]], 'checkPermissions' => false]);
            }
        }

        // Delete contact fields
        $values = [
            // Names
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
            'prefix_id' => null,
            'suffix_id' => null,
            'formal_title' => null,
            'nick_name' => null,
            'sort_name' => null,
            'display_name' => null,
            // IDs
            'external_identifier' => null,
            'legal_identifier' => null,
            'api_key' => null,
            'user_unique_id' => null,
            // Greetings
            'communication_style_id' => null,
            'email_greeting_id' => null,
            'email_greeting_custom' => null,
            'email_greeting_display' => null,
            'postal_greeting_id' => null,
            'postal_greeting_custom' => null,
            'postal_greeting_display' => null,
            'addressee_id' => null,
            'addressee_custom' => null,
            'addressee_display' => null,
            // Privacy fields
            'do_not_email' => true,
            'do_not_phone' => true,
            'do_not_mail' => true,
            'do_not_sms' => true,
            'do_not_trade' => true,
            // Other
            'contact_sub_type' => null,
            'image_URL' => null,
            'source' => null,
            'gender_id' => null,
            'birth_date' => null,
            'deceased_date' => null,
            'job_title' => null,
            'employer_id' => null,
        ];
        \Civi\RcBase\ApiWrapper\Update::contact($this->contactID, $values);

        $result[] = Get::entityByID('Contact', $this->contactID);
    }
}
