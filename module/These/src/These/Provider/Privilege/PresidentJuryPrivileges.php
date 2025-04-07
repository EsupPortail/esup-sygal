<?php

namespace These\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class PresidentJuryPrivileges extends Privileges
{
    public const PRESIDENT_GESTION           = 'gestion-president-gestion-president';
    public const PRESIDENT_MODIFIER_MAIL     = 'gestion-president-modifier-mail-president';
    public const PRESIDENT_NOTIFIER          = 'gestion-president-notifier-president';
}