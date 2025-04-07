<?php

namespace Individu\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class IndividuPrivileges extends Privileges
{
    /**
     * Individus
     */
    public const INDIVIDU_LISTER = 'individu-lister';
    public const INDIVIDU_CONSULTER = 'individu-consulter';
    public const INDIVIDU_AJOUTER = 'individu-ajouter';
    public const INDIVIDU_MODIFIER = 'individu-modifier';
    public const INDIVIDU_SUPPRIMER = 'individu-supprimer';

    /**
     * Compléments aux individus
     */
    public const INDIVIDU_COMPLMENT_INDEX          = 'individu-individucompl_index';
    public const INDIVIDU_COMPLMENT_AFFICHER       = 'individu-individucompl_afficher';
    public const INDIVIDU_COMPLMENT_MODIFIER       = 'individu-individucompl_modifier';
    public const INDIVIDU_COMPLMENT_SUPPRIMER      = 'individu-individucompl_supprimer';
}