<?php

namespace Application\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class UtilisateurPrivileges extends Privileges
{
    public const UTILISATEUR_CONSULTATION                  = 'utilisateur-consultation';
    public const UTILISATEUR_MODIFICATION                  = 'utilisateur-modification';
    public const UTILISATEUR_ATTRIBUTION_ROLE              = 'utilisateur-attribution-role';
    public const UTILISATEUR_CREATE_FROM_INDIVIDU          = 'utilisateur-create_from_individu';
}