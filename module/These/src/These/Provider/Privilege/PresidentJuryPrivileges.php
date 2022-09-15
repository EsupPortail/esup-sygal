<?php

namespace These\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class PresidentJuryPrivileges extends Privileges
{
    const PRESIDENT_GESTION           = 'gestion-president-gestion-president';
    const PRESIDENT_MODIFIER_MAIL     = 'gestion-president-modifier-mail-president';
    const PRESIDENT_NOTIFIER          = 'gestion-president-notifier-president';
}