<?php

namespace Depot\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class ValidationPrivileges extends Privileges
{
    public const THESE_VALIDATION_RDV_BU                   = 'validation-rdv-bu';
    public const THESE_VALIDATION_RDV_BU_SUPPR             = 'validation-rdv-bu-suppression';
    public const VALIDATION_PAGE_DE_COUVERTURE             = 'validation-page-de-couverture';
    public const VALIDATION_PAGE_DE_COUVERTURE_SUPPR       = 'validation-page-de-couverture-suppr';
    public const VALIDATION_DEPOT_THESE_CORRIGEE           = 'validation-depot-these-corrigee';
    public const VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR     = 'validation-depot-these-corrigee-suppression';
    public const VALIDATION_CORRECTION_THESE               = 'validation-correction-these';
    public const VALIDATION_CORRECTION_THESE_SUPPR         = 'validation-correction-these-suppression';
    public const VALIDATION_VERSION_PAPIER_CORRIGEE        = 'validation-version-papier-corrigee';
}