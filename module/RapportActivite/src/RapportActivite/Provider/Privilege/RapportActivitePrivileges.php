<?php

namespace RapportActivite\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class RapportActivitePrivileges extends Privileges
{
    const RAPPORT_ACTIVITE_LISTER_TOUT = 'rapport-activite-lister-tout';
    const RAPPORT_ACTIVITE_LISTER_SIEN = 'rapport-activite-lister-sien';
    const RAPPORT_ACTIVITE_RECHERCHER_TOUT = 'rapport-activite-rechercher-tout';
    const RAPPORT_ACTIVITE_RECHERCHER_SIEN = 'rapport-activite-rechercher-sien';
    const RAPPORT_ACTIVITE_TELECHARGER_ZIP = 'rapport-activite-telecharger-zip';

    const RAPPORT_ACTIVITE_TELEVERSER_TOUT = 'rapport-activite-televerser-tout';
    const RAPPORT_ACTIVITE_TELEVERSER_SIEN = 'rapport-activite-televerser-sien';
    const RAPPORT_ACTIVITE_SUPPRIMER_TOUT = 'rapport-activite-supprimer-tout';
    const RAPPORT_ACTIVITE_SUPPRIMER_SIEN = 'rapport-activite-supprimer-sien';
    const RAPPORT_ACTIVITE_TELECHARGER_TOUT = 'rapport-activite-telecharger-tout';
    const RAPPORT_ACTIVITE_TELECHARGER_SIEN = 'rapport-activite-telecharger-sien';

    // validation
    const RAPPORT_ACTIVITE_VALIDER_TOUT = 'rapport-activite-valider-tout';
    const RAPPORT_ACTIVITE_VALIDER_SIEN = 'rapport-activite-valider-sien';
    const RAPPORT_ACTIVITE_DEVALIDER_TOUT = 'rapport-activite-devalider-tout';
    const RAPPORT_ACTIVITE_DEVALIDER_SIEN = 'rapport-activite-devalider-sien';

    // avis
    const RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT = 'rapport-activite-ajouter-avis-tout';
    const RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN = 'rapport-activite-ajouter-avis-sien';
    const RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT = 'rapport-activite-modifier-avis-tout';
    const RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN = 'rapport-activite-modifier-avis-sien';
    const RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT = 'rapport-activite-supprimer-avis-tout';
    const RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN = 'rapport-activite-supprimer-avis-sien';

}
