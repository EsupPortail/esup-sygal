<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class SubstitutionPrivileges extends Privileges
{
    const SUBSTITUION_AUTOMATIQUE                    = 'substitution-automatique';
    const SUBSTITUION_CONSULTATION_ETABLISSEMENT     = 'substitution-consultation-etablissement';
    const SUBSTITUION_MODIFICATION_ETABLISSEMENT     = 'substitution-modification-etablissement';
    const SUBSTITUION_CONSULTATION_ECOLE             = 'substitution-consultation-ecole';
    const SUBSTITUION_MODIFICATION_ECOLE             = 'substitution-modification-ecole';
    const SUBSTITUION_CONSULTATION_UNITE             = 'substitution-consultation-unite';
    const SUBSTITUION_MODIFICATION_UNITE             = 'substitution-modification-unite';


}