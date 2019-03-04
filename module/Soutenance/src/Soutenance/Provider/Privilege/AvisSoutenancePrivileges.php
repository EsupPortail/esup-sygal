<?php

namespace Soutenance\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class AvisSoutenancePrivileges extends Privileges
{
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    const SOUTENANCE_AVIS_VISUALISER                      = 'soutenance-avis-visualisation';
    const SOUTENANCE_AVIS_MODIFIER                        = 'soutenance-avis-modification';
    const SOUTENANCE_AVIS_ANNULER                         = 'soutenance-avis-annuler';
    const SOUTENANCE_AVIS_NOTIFIER                        = 'soutenance-avis-notifier';
}