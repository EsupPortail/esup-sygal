<?php

namespace Structure\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class SubstitutionPrivileges extends Privileges
{
    const SUBSTITUION_AUTOMATIQUE                    = 'substitution-automatique';


    const SUBSTITUTION_CONSULTATION_TOUTES_STRUCTURES   = 'substitution-consultation-toutes-structures';
    const SUBSTITUTION_CONSULTATION_SA_STRUCTURE        = 'substitution-consultation-sa-structure';
    const SUBSTITUTION_MODIFICATION_TOUTES_STRUCTURES   = 'substitution-modification-toutes-structures';
    const SUBSTITUTION_MODIFICATION_SA_STRUCTURE        = 'substitution-modification-sa-structure';
}