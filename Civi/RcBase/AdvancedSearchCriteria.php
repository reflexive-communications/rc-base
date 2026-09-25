<?php

namespace Civi\RcBase;

use Civi;

/**
 * Advanced Search criteria user-interface adjustments.
 */
final class AdvancedSearchCriteria
{
    /**
     * Add resources required by Advanced Search criteria panes.
     */
    public static function addResources(): void
    {
        Civi::resources()->addScriptFile('rc-base', 'js/advanced-search-criteria.js');
    }
}
