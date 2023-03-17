<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class FormationPrivileges extends Privileges {

    const FORMATION_INDEX      = 'formation_formation-index';
    const FORMATION_AFFICHER   = 'formation_formation-afficher';
    const FORMATION_AJOUTER    = 'formation_formation-ajouter';
    const FORMATION_MODIFIER   = 'formation_formation-modifier';
    const FORMATION_HISTORISER = 'formation_formation-historiser';
    const FORMATION_SUPPRIMER  = 'formation_formation-supprimer';
}