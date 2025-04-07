<?php

namespace Structure\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class EtablissementPrivileges extends Privileges
{
    public const ETABLISSEMENT_CONSULTATION = 'etablissement-consultation';
    public const ETABLISSEMENT_MODIFICATION = 'etablissement-modification';
}