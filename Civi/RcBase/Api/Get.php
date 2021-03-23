<?php

namespace Civi\RcBase\Api;

use API_Exception;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\ActivityContact;
use Civi\Api4\Address;
use Civi\Api4\Contact;
use Civi\Api4\Email;
use Civi\Api4\Generic\Result;
use Civi\Api4\LocationType;
use Civi\Api4\Phone;
use Civi\Api4\Relationship;

/**
 * Common Get Actions
 *
 * Wrapper around APIv4
 *
 * @package  rc-base
 * @author   Sandor Semsey <sandor@es-progress.hu>
 * @license  AGPL-3.0
 */
class Get
{
    /**
     * Parse result set, return first row
     *
     * @param Result $results Api4 Result set
     * @param string $field Field to return
     *
     * @return mixed|null
     */
    protected static function parseResultsFirst(Result $results, string $field = "")
    {
        // Get first result row
        $result = $results->first();

        // Empty result
        if (!is_array($result)) {
            return null;
        }

        // No field specified --> return all fields
        if (empty($field)) {
            return $result;
        }

        return $result[$field];
    }

    /**
     * Get contact ID from email
     *
     * @param string $email Email address
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contact ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function contactIDFromEmail(string $email, bool $check_permissions = false): ?int
    {
        $results = Email::get($check_permissions)
            ->addSelect('contact_id')
            ->addWhere('email', '=', $email)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'contact_id');
    }

    /**
     * Get contact ID from external ID
     *
     * @param string $external_id External ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Contact ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function contactIDFromExternalID(string $external_id, bool $check_permissions = false): ?int
    {
        $results = Contact::get($check_permissions)
            ->addSelect('id')
            ->addWhere('external_identifier', '=', $external_id)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Retrieve contact data
     *
     * @param int $contact_id Contact ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array|null Contact data on success, null on fail
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public static function contactData(int $contact_id, bool $check_permissions = false): ?array
    {
        CRM_Wrapi_Processor_Base::validateInput($contact_id, 'id', 'Contact ID');

        $results = Contact::get($check_permissions)
            ->addSelect('*')
            ->addWhere('id', '=', $contact_id)
            ->setLimit(1)
            ->execute();

        return $results->first();
    }

    /**
     * Get Email ID from contact and email type
     *
     * @param int $contact_id Contact ID
     * @param int $loc_type_id Location type id (Home, Main, etc...)
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Email ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function emailID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        $results = Email::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Get Phone ID from contact and phone type
     *
     * @param int $contact_id Contact ID
     * @param int $loc_type_id Location type id (Home, Main, etc...)
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Phone ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function phoneID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        $results = Phone::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Get Address ID from contact and phone type
     *
     * @param int $contact_id Contact ID
     * @param int $loc_type_id Location type id (Home, Main, etc...)
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Address ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function addressID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        $results = Address::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Get Relationship ID from contact and phone type
     *
     * @param int $contact_id Contact ID
     * @param int $other_contact_id Other contact ID (of the relation)
     * @param int $relationship_type_id Relationship type ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Relationship ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function relationshipID(
        int $contact_id,
        int $other_contact_id,
        int $relationship_type_id,
        bool $check_permissions = false
    ): ?int {
        $results = Relationship::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id_a', '=', $contact_id)
            ->addWhere('contact_id_b', '=', $other_contact_id)
            ->addWhere('relationship_type_id', '=', $relationship_type_id)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Get ID of default Location type
     *
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null Location type ID if found, null if not found
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function defaultLocationTypeID(bool $check_permissions = false): ?int
    {
        $results = LocationType::get($check_permissions)
            ->addSelect('id')
            ->addWhere('is_default', '=', true)
            ->setLimit(1)
            ->execute();

        return $results->first()['id'];
    }

    /**
     * Get All Activity for a contact, where the contact is the target of activity
     *
     * @param int $contact_id Contact ID
     * @param int $activity_type_id Optionally filter activities by this type
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Array of Activity IDs
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     */
    public static function allActivity(
        int $contact_id,
        int $activity_type_id = 0,
        bool $check_permissions = false
    ): array {
        $activities = [];

        // record_type_id=3 means contact is the target of activity
        $query = ActivityContact::get($check_permissions)
            ->addSelect('activity.*')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3);

        // Add filter
        if ($activity_type_id > 0) {
            $query = $query->addWhere('activity.activity_type_id', '=', $activity_type_id);
        }

        $results = $query->execute();

        foreach ($results as $activity) {
            $activities[] = $activity;
        }

        return $activities;
    }
}
