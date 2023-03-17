<?php

namespace Structure\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class EtablissementPrivileges extends Privileges
{
    const ETABLISSEMENT_CONSULTATION = 'etablissement-consultation';
    const ETABLISSEMENT_MODIFICATION = 'etablissement-modification';
}