<?php

namespace Individu\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class IndividuPrivileges extends Privileges
{
    /**
     * Individus
     */
    const INDIVIDU_LISTER = 'individu-lister';
    const INDIVIDU_CONSULTER = 'individu-consulter';
    const INDIVIDU_AJOUTER = 'individu-ajouter';
    const INDIVIDU_MODIFIER = 'individu-modifier';
    const INDIVIDU_SUPPRIMER = 'individu-supprimer';

    /**
     * Compléments aux individus
     */
    const INDIVIDU_COMPLMENT_INDEX          = 'individu-individucompl_index';
    const INDIVIDU_COMPLMENT_AFFICHER       = 'individu-individucompl_afficher';
    const INDIVIDU_COMPLMENT_MODIFIER       = 'individu-individucompl_modifier';
    const INDIVIDU_COMPLMENT_SUPPRIMER      = 'individu-individucompl_supprimer';
}