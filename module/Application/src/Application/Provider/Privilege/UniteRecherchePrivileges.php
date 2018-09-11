<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class UniteRecherchePrivileges extends Privileges
{
    const UNITE_RECH_CONSULTATION = 'unite-recherche-consultation';
    const UNITE_RECH_CREATION = 'unite-recherche-creation';
    const UNITE_RECH_MODIFICATION = 'unite-recherche-modification';
}