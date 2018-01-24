<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class UtilisateurPrivileges extends Privileges
{
    const UTILISATEUR_CONSULTATION                  = 'utilisateur-consultation';
    const UTILISATEUR_MODIFICATION                  = 'utilisateur-modification';
    const UTILISATEUR_ATTRIBUTION_ROLE              = 'utilisateur-attribution-role';
}