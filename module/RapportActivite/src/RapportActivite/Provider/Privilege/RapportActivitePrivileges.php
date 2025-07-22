<?php

namespace RapportActivite\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class RapportActivitePrivileges extends Privileges
{
    public const RAPPORT_ACTIVITE_LISTER_TOUT = 'rapport-activite-lister-tout';
    public const RAPPORT_ACTIVITE_LISTER_SIEN = 'rapport-activite-lister-sien';
    public const RAPPORT_ACTIVITE_TELECHARGER_ZIP = 'rapport-activite-telecharger-zip';

    public const RAPPORT_ACTIVITE_CONSULTER_TOUT = 'rapport-activite-consulter-tout';
    public const RAPPORT_ACTIVITE_CONSULTER_SIEN = 'rapport-activite-consulter-sien';
    public const RAPPORT_ACTIVITE_AJOUTER_TOUT = 'rapport-activite-ajouter-tout';
    public const RAPPORT_ACTIVITE_AJOUTER_SIEN = 'rapport-activite-ajouter-sien';
    public const RAPPORT_ACTIVITE_MODIFIER_TOUT = 'rapport-activite-modifier-tout';
    public const RAPPORT_ACTIVITE_MODIFIER_SIEN = 'rapport-activite-modifier-sien';
    public const RAPPORT_ACTIVITE_TELEVERSER_TOUT = 'rapport-activite-televerser-tout';
    public const RAPPORT_ACTIVITE_TELEVERSER_SIEN = 'rapport-activite-televerser-sien';
    public const RAPPORT_ACTIVITE_SUPPRIMER_TOUT = 'rapport-activite-supprimer-tout';
    public const RAPPORT_ACTIVITE_SUPPRIMER_SIEN = 'rapport-activite-supprimer-sien';
    public const RAPPORT_ACTIVITE_SUPPRIMER_TOUT_FORCER = 'rapport-activite-supprimer-tout-forcer'; // même si validation/avis existants
    public const RAPPORT_ACTIVITE_SUPPRIMER_SIEN_FORCER = 'rapport-activite-supprimer-sien-forcer'; //
    public const RAPPORT_ACTIVITE_TELECHARGER_TOUT = 'rapport-activite-telecharger-tout';
    public const RAPPORT_ACTIVITE_TELECHARGER_SIEN = 'rapport-activite-telecharger-sien';
    public const RAPPORT_ACTIVITE_GENERER_TOUT = 'rapport-activite-generer-tout';
    public const RAPPORT_ACTIVITE_GENERER_SIEN = 'rapport-activite-generer-sien';

    // validation
    public const RAPPORT_ACTIVITE_VALIDER_TOUT = 'rapport-activite-valider-tout';
    public const RAPPORT_ACTIVITE_VALIDER_SIEN = 'rapport-activite-valider-sien';
    public const RAPPORT_ACTIVITE_DEVALIDER_TOUT = 'rapport-activite-devalider-tout';
    public const RAPPORT_ACTIVITE_DEVALIDER_SIEN = 'rapport-activite-devalider-sien';

    // avis
    public const RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT = 'rapport-activite-ajouter-avis-tout';
    public const RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN = 'rapport-activite-ajouter-avis-sien';
    public const RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT = 'rapport-activite-modifier-avis-tout';
    public const RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN = 'rapport-activite-modifier-avis-sien';
    public const RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT = 'rapport-activite-supprimer-avis-tout';
    public const RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN = 'rapport-activite-supprimer-avis-sien';

}
