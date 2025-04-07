<?php

namespace Soutenance\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class AvisSoutenancePrivileges extends Privileges
{
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    public const AVIS_VISUALISER = 'soutenance-avis-visualisation';
    public const AVIS_MODIFIER = 'soutenance-avis-modification';
    public const AVIS_ANNULER = 'soutenance-avis-annuler';
    public const AVIS_NOTIFIER = 'soutenance-avis-notifier';
}