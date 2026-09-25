<?php

namespace Civi\RcBase;

/**
 * Applies shared defaults to contacts.
 */
class ContactDefaults
{
    /**
     * CiviCRM contact types handled by the Contact BAO.
     */
    private const CONTACT_TYPES = [
        'Household',
        'Individual',
        'Organization',
    ];

    /**
     * Enforce No Bulk Emails for every newly created contact.
     *
     * @param string $operation
     * @param string $objectName
     * @param array $params
     */
    public static function applyNoBulkDefault(string $operation, string $objectName, array &$params): void
    {
        if ($operation !== 'create' || !in_array($objectName, self::CONTACT_TYPES, true)) {
            return;
        }

        $params['is_opt_out'] = true;
    }
}
