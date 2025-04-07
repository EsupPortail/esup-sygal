<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class FormationPrivileges extends Privileges {

    public const FORMATION_INDEX      = 'formation_formation-index';
    public const FORMATION_AFFICHER   = 'formation_formation-afficher';
    public const FORMATION_AJOUTER    = 'formation_formation-ajouter';
    public const FORMATION_MODIFIER   = 'formation_formation-modifier';
    public const FORMATION_HISTORISER = 'formation_formation-historiser';
    public const FORMATION_SUPPRIMER  = 'formation_formation-supprimer';
}