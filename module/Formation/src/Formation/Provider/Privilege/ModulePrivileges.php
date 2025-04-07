<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class ModulePrivileges extends Privileges {

    public const MODULE_INDEX      = 'formation_module-index';
    public const MODULE_AFFICHER   = 'formation_module-afficher';
    public const MODULE_AJOUTER    = 'formation_module-ajouter';
    public const MODULE_MODIFIER   = 'formation_module-modifier';
    public const MODULE_HISTORISER = 'formation_module-historiser';
    public const MODULE_SUPPRIMER  = 'formation_module-supprimer';
    public const MODULE_CATALOGUE  = 'formation_module-catalogue';
}