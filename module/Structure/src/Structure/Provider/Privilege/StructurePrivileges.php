<?php

namespace Structure\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class StructurePrivileges extends Privileges
{
    const STRUCTURE_CONSULTATION_TOUTES_STRUCTURES  = 'structure-consultation-de-toutes-les-structures';
    const STRUCTURE_CONSULTATION_SES_STRUCTURES     = 'structure-consultation-de-ses-structures';
    const STRUCTURE_MODIFICATION_TOUTES_STRUCTURES  = 'structure-modification-de-toutes-les-structures';
    const STRUCTURE_MODIFICATION_SES_STRUCTURES     = 'structure-modification-de-ses-structures';
    const STRUCTURE_CREATION_ETAB                   = 'structure-creation-etab';
    const STRUCTURE_CREATION_ED                     = 'structure-creation-ed';
    const STRUCTURE_CREATION_UR                     = 'structure-creation-ur';

}