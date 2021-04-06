<?php

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\ActivityContact;
use Civi\Api4\Address;
use Civi\Api4\Contact;
use Civi\Api4\Email;
use Civi\Api4\EntityTag;
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
class CRM_RcBase_Api_Get
{
    /**
     * Record type id when contact is the assignee of the activity
     */
    public const ACTIVITY_RECORD_TYPE_ASSIGNEE = 1;

    /**
     * Record type id when contact is the source of the activity
     */
    public const ACTIVITY_RECORD_TYPE_SOURCE = 2;

    /**
     * Record type id when contact is the target of the activity
     */
    public const ACTIVITY_RECORD_TYPE_TARGET = 3;

    /**
     * Parse result set, return first row
     *
     * @param Result $results Api4 Result set
     * @param string $field Field to return
     *
     * @return mixed|null
     */
    protected static function parseResultsFirst(Result $results, string $field = '')
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
        // Return early
        if (empty($email)) {
            return null;
        }

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
        // Return early
        if (empty($external_id)) {
            return null;
        }

        $results = Contact::get($check_permissions)
            ->addSelect('id')
            ->addWhere('external_identifier', '=', $external_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
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
        if ($contact_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = Contact::get($check_permissions)
            ->addSelect('*')
            ->addWhere('id', '=', $contact_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results);
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
     * @throws CRM_Core_Exception
     */
    public static function emailID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        if ($contact_id < 1 || $loc_type_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = Email::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
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
     * @throws CRM_Core_Exception
     */
    public static function phoneID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        if ($contact_id < 1 || $loc_type_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = Phone::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
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
     * @throws CRM_Core_Exception
     */
    public static function addressID(int $contact_id, int $loc_type_id, bool $check_permissions = false): ?int
    {
        if ($contact_id < 1 || $loc_type_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = Address::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('location_type_id', '=', $loc_type_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
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
     * @throws CRM_Core_Exception
     */
    public static function relationshipID(
        int $contact_id,
        int $other_contact_id,
        int $relationship_type_id,
        bool $check_permissions = false
    ): ?int {
        if ($contact_id < 1 || $other_contact_id < 1 || $relationship_type_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = Relationship::get($check_permissions)
            ->addSelect('id')
            ->addWhere('contact_id_a', '=', $contact_id)
            ->addWhere('contact_id_b', '=', $other_contact_id)
            ->addWhere('relationship_type_id', '=', $relationship_type_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
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

        return self::parseResultsFirst($results, 'id');
    }

    /**
     * Get All Activity for a contact
     *
     * @param int $contact_id Contact ID
     * @param int $record_type_id Contact role in activity (source, target, assignee)
     * @param int $activity_type_id Optionally filter activities by this type
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return array Array of Activity IDs
     *
     * @throws API_Exception
     * @throws UnauthorizedException
     * @throws CRM_Core_Exception
     */
    public static function allActivity(
        int $contact_id,
        int $record_type_id,
        int $activity_type_id = 0,
        bool $check_permissions = false
    ): array {
        $activities = [];

        if ($contact_id < 1 || $record_type_id < 1 || $activity_type_id < 0) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $query = ActivityContact::get($check_permissions)
            ->addSelect('activity.*')
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', $record_type_id);

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

    /**
     * Check if tag is applied to a contact
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @param bool $check_permissions Should we check permissions (ACLs)?
     *
     * @return int|null EntityTag ID if found, null if not found
     *
     * @throws API_Exception
     * @throws CRM_Core_Exception
     * @throws UnauthorizedException
     */
    public static function contactHasTag(int $contact_id, int $tag_id, bool $check_permissions = false): ?int
    {
        if ($contact_id < 1 || $tag_id < 1) {
            throw new CRM_Core_Exception('Invalid ID.');
        }

        $results = EntityTag::get($check_permissions)
            ->addSelect('id')
            ->addWhere('entity_id', '=', $contact_id)
            ->addWhere('entity_table', '=', 'civicrm_contact')
            ->addWhere('tag_id', '=', $tag_id)
            ->setLimit(1)
            ->execute();

        return self::parseResultsFirst($results, 'id');
    }
}
