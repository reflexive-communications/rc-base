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
     * @param string $operation
     * @param string $objectName
     * @param array $params
     *
     * @return void
     */
    public static function applyNoBulkDefault(string $operation, string $objectName, array &$params): void
    {
        if ($operation !== 'create' || !in_array($objectName, self::CONTACT_TYPES, true) || array_key_exists('is_opt_out', $params)) {
            return;
        }

        $params['is_opt_out'] = true;
    }
}
