<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class SeancePrivileges extends Privileges {

    public const SEANCE_INDEX      = 'formation_seance-index';
    public const SEANCE_AFFICHER   = 'formation_seance-afficher';
    public const SEANCE_AJOUTER    = 'formation_seance-ajouter';
    public const SEANCE_MODIFIER   = 'formation_seance-modifier';
    public const SEANCE_HISTORISER = 'formation_seance-historiser';
    public const SEANCE_SUPPRIMER  = 'formation_seance-supprimer';
    public const SEANCE_PRESENCE   = 'formation_seance-renseigner_presence';
}