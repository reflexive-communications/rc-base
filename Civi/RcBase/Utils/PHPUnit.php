<?php

namespace Civi\RcBase\Utils;

use Civi\Api4\UFMatch;
use Civi\RcBase\ApiWrapper\Get;
use CRM_Core_Session;
use CRM_RcBase_Api_Create;

/**
 * Utilities for unit-testing
 * Please don't use in production code!
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class PHPUnit
{
    /**
     * Static counter
     *
     * @var int
     */
    public static int $counter = 1;

    /**
     * Supply a monotonic incremented counter
     *
     * @return int
     */
    public static function nextCounter(): int
    {
        return self::$counter++;
    }

    /**
     * Simulate a logged in system user
     *
     * @param int $uf_id UF ID for system contact (e.g. Drupal user ID)
     *
     * @return int Contact ID
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     * @throws \Civi\RcBase\Exception\APIException
     */
    public static function createLoggedInUser(int $uf_id = 1): int
    {
        $results = UFMatch::get(false)
            ->addSelect('contact_id')
            ->addWhere('uf_id', '=', $uf_id)
            ->setLimit(1)
            ->execute();
        $contact_id = Get::parseResultsFirst($results, 'contact_id');

        // User not exists --> create
        if (is_null($contact_id)) {
            $contact_id = CRM_RcBase_Api_Create::contact([
                'first_name' => 'logged_in',
                'last_name' => 'user',
                'contact_type' => 'Individual',
            ]);
            CRM_RcBase_Api_Create::email(
                $contact_id,
                [
                    'email' => "logged.in.user{$uf_id}@testing.com",
                    'is_primary' => true,
                ],
            );

            // Create UF match, uf_id is the ID of the user in the CMS
            // Use ID #1, simulate system user
            CRM_RcBase_Api_Create::entity('UFMatch', [
                'uf_id' => $uf_id,
                'contact_id' => $contact_id,
            ]);
        }

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
    public static function createIndividual(int $counter = 0, array $extra = []): int
    {
        if ($counter == 0) {
            $counter = self::nextCounter();
        }

        $default = [
            'contact_type' => 'Individual',
            'first_name' => "user_{$counter}",
            'external_identifier' => "ext_{$counter}",
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
    public static function createIndividualWithEmail(int $counter = 0, array $extra_contact = [], array $extra_email = []): int
    {
        if ($counter == 0) {
            $counter = self::nextCounter();
        }

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
