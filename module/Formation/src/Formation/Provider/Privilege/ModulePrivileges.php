<?php

namespace Formation\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class ModulePrivileges extends Privileges {

    const MODULE_INDEX      = 'formation_module-index';
    const MODULE_AFFICHER   = 'formation_module-afficher';
    const MODULE_AJOUTER    = 'formation_module-ajouter';
    const MODULE_MODIFIER   = 'formation_module-modifier';
    const MODULE_HISTORISER = 'formation_module-historiser';
    const MODULE_SUPPRIMER  = 'formation_module-supprimer';
    const MODULE_CATALOGUE  = 'formation_module-catalogue';
}