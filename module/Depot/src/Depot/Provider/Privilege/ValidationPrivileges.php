<?php

namespace Depot\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class ValidationPrivileges extends Privileges
{
    const THESE_VALIDATION_RDV_BU                   = 'validation-rdv-bu';
    const THESE_VALIDATION_RDV_BU_SUPPR             = 'validation-rdv-bu-suppression';
    const VALIDATION_PAGE_DE_COUVERTURE             = 'validation-page-de-couverture';
    const VALIDATION_PAGE_DE_COUVERTURE_SUPPR       = 'validation-page-de-couverture-suppr';
    const VALIDATION_DEPOT_THESE_CORRIGEE           = 'validation-depot-these-corrigee';
    const VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR     = 'validation-depot-these-corrigee-suppression';
    const VALIDATION_CORRECTION_THESE               = 'validation-correction-these';
    const VALIDATION_CORRECTION_THESE_SUPPR         = 'validation-correction-these-suppression';
    const VALIDATION_VERSION_PAPIER_CORRIGEE        = 'validation-version-papier-corrigee';
}