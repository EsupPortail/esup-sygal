<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class ValidationPrivileges extends Privileges
{
    const THESE_VALIDATION_RDV_BU                   = 'validation-rdv-bu';
    const THESE_VALIDATION_RDV_BU_SUPPR             = 'validation-rdv-bu-suppression';
    const VALIDATION_DEPOT_THESE_CORRIGEE           = 'validation-depot-these-corrigee';
    const VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR     = 'validation-depot-these-corrigee-suppression';
    const VALIDATION_CORRECTION_THESE               = 'validation-correction-these';
    const VALIDATION_CORRECTION_THESE_SUPPR         = 'validation-correction-these-suppression';
}