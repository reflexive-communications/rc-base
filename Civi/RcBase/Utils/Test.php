<?php

namespace Civi\RcBase\Utils;

use CRM_Core_Session;
use CRM_RcBase_Api_Create;

/**
 * Utilities for unit-testing
 */
class Test
{
    /**
     * Simulate a logged in user
     *
     * @return int Contact ID
     * @throws \CRM_Core_Exception
     */
    public static function createLoggedInUser(): int
    {
        $contact_id = CRM_RcBase_Api_Create::contact([
            'first_name' => 'logged_in',
            'last_name' => 'user',
            'contact_type' => 'Individual',
        ]);
        CRM_RcBase_Api_Create::email(
            $contact_id,
            [
                'email' => 'loggedinuser@testing.com',
                'is_primary' => 1,
            ],
        );

        // Create UF match, uf_id is the ID of the user in the CMS
        // Now it is 42, it don't have to be a real user ID
        CRM_RcBase_Api_Create::entity('UFMatch', [
            'uf_id' => 42,
            'contact_id' => $contact_id,
        ]);

        // Set ID in session
        $session = CRM_Core_Session::singleton();
        $session->set('userID', $contact_id);

        return $contact_id;
    }

    /**
     * Create contact
     *
     * @param int $counter Contact serial number
     * @param array $extra Extra parameters to Contact entity
     *
     * @return int Contact ID
     * @throws \CRM_Core_Exception
     */
    public static function createIndividual(int $counter, array $extra = []): int
    {
        $default = [
            'contact_type' => 'Individual',
            'first_name' => "user_{$counter}",
            'middle_name' => 'middle',
            'last_name' => 'Test',
            'external_identifier' => $counter,
        ];

        return CRM_RcBase_Api_Create::contact(array_merge($default, $extra));
    }

    /**
     * Create contact with email address
     *
     * @param int $counter Contact serial number
     * @param array $extra_contact Extra parameters to Contact entity
     * @param array $extra_email Extra parameters to Email entity
     *
     * @return int Contact ID
     * @throws \CRM_Core_Exception
     */
    public static function createIndividualWithEmail(int $counter, array $extra_contact = [], array $extra_email = []): int
    {
        $default = [
            'location_type_id' => 1,
            'email' => "user_{$counter}@test.com",
            'is_primary' => true,
        ];

        $contact_id = self::createIndividual($counter, $extra_contact);
        CRM_RcBase_Api_Create::email($contact_id, array_merge($default, $extra_email));

        return $contact_id;
    }
}
