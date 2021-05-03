<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class RapportPrivileges extends Privileges
{
    // NB : catégorie = 'rapport-activite'

    const RAPPORT_ACTIVITE_LISTER_TOUT = 'rapport-activite-lister-tout';
    const RAPPORT_ACTIVITE_LISTER_SIEN = 'rapport-activite-lister-sien';
    const RAPPORT_ACTIVITE_TELEVERSER_TOUT = 'rapport-activite-televerser-tout';
    const RAPPORT_ACTIVITE_TELEVERSER_SIEN = 'rapport-activite-televerser-sien';
    const RAPPORT_ACTIVITE_SUPPRIMER_TOUT = 'rapport-activite-supprimer-tout';
    const RAPPORT_ACTIVITE_SUPPRIMER_SIEN = 'rapport-activite-supprimer-sien';
    const RAPPORT_ACTIVITE_RECHERCHER_TOUT = 'rapport-activite-rechercher-tout';
    const RAPPORT_ACTIVITE_RECHERCHER_SIEN = 'rapport-activite-rechercher-sien';
    const RAPPORT_ACTIVITE_TELECHARGER_TOUT = 'rapport-activite-telecharger-tout';
    const RAPPORT_ACTIVITE_TELECHARGER_SIEN = 'rapport-activite-telecharger-sien';
    const RAPPORT_ACTIVITE_TELECHARGER_ZIP = 'rapport-activite-telecharger-zip';
}
