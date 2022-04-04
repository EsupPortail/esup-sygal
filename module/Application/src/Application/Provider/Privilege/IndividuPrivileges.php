<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class IndividuPrivileges extends Privileges
{
    /** Gestion des complements d'individus */
    const INDIVIDU_COMPLMENT_INDEX          = 'individu-individucompl_index';
    const INDIVIDU_COMPLMENT_AFFICHER       = 'individu-individucompl_afficher';
    const INDIVIDU_COMPLMENT_MODIFIER       = 'individu-individucompl_modifier';
    const INDIVIDU_COMPLMENT_SUPPRIMER      = 'individu-individucompl_supprimer';
}