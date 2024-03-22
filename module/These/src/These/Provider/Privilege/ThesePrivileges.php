<?php

namespace These\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations concernant les thèses.
 */
class ThesePrivileges extends Privileges
{
    /**
     * Tout faire !
     */
    const THESE_TOUT_FAIRE                          = 'these-tout-faire';

    /**
     * Recherche
     */
    const THESE_RECHERCHE                           = 'these-recherche';
    const THESE_EXPORT_CSV                          = 'these-export-csv';

    /**
     * Import
     */
    const THESE_REFRESH                             = 'these-refresh-these';

    /**
     * Fiche ("Thèse")
     */
    const THESE_CONSULTATION_FICHE                  = 'these-consultation-fiche';

    /** NOUVEAU PRIVILIEGES DE LA POC *********************************************************************************/

    const THESE_CONSULTATION_TOUTES_THESES          = 'these-consultation-de-toutes-les-theses';
    const THESE_CONSULTATION_SES_THESES             = 'these-consultation-de-ses-theses';
    const THESE_MODIFICATION_TOUTES_THESES          = 'these-modification-de-toutes-les-theses';
    const THESE_MODIFICATION_SES_THESES             = 'these-modification-de-ses-theses';
    const THESE_MODIFICATION_DOMAINES_HAL_THESE     = 'these-modification-domaines-hal-these';
}