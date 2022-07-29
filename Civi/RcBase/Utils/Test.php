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
     *
     * @throws \CRM_Core_Exception
     */
    public static function createLoggedInUser(): int
    {
        $contact_id = CRM_RcBase_Api_Create::contact([
            'first' => 'logged_in',
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
}
