<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class SeancePrivileges extends Privileges {

    const SEANCE_INDEX      = 'formation_seance-index';
    const SEANCE_AFFICHER   = 'formation_seance-afficher';
    const SEANCE_AJOUTER    = 'formation_seance-ajouter';
    const SEANCE_MODIFIER   = 'formation_seance-modifier';
    const SEANCE_HISTORISER = 'formation_seance-historiser';
    const SEANCE_SUPPRIMER  = 'formation_seance-supprimer';
    const SEANCE_PRESENCE   = 'formation_seance-renseigner_presence';
}