<?php

namespace Soutenance\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class AvisSoutenancePrivileges extends Privileges
{
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    const AVIS_VISUALISER                      = 'soutenance-avis-visualisation';
    const AVIS_MODIFIER                        = 'soutenance-avis-modification';
    const AVIS_ANNULER                         = 'soutenance-avis-annuler';
    const AVIS_NOTIFIER                        = 'soutenance-avis-notifier';
}