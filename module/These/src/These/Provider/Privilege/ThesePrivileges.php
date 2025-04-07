<?php

namespace These\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations concernant les thèses.
 */
class ThesePrivileges extends Privileges
{
    /**
     * Tout faire !
     */
    public const THESE_TOUT_FAIRE                          = 'these-tout-faire';

    /**
     * Recherche
     */
    public const THESE_RECHERCHE                           = 'these-recherche';
    public const THESE_EXPORT_CSV                          = 'these-export-csv';

    /**
     * Import
     */
    public const THESE_REFRESH                             = 'these-refresh-these';

    /**
     * Fiche ("Thèse")
     */
    public const THESE_CONSULTATION_FICHE                  = 'these-consultation-fiche';

    /** NOUVEAU PRIVILIEGES DE LA POC *********************************************************************************/

    public const THESE_CONSULTATION_TOUTES_THESES          = 'these-consultation-de-toutes-les-theses';
    public const THESE_CONSULTATION_SES_THESES             = 'these-consultation-de-ses-theses';
    public const THESE_MODIFICATION_TOUTES_THESES          = 'these-modification-de-toutes-les-theses';
    public const THESE_MODIFICATION_SES_THESES             = 'these-modification-de-ses-theses';
    public const THESE_MODIFICATION_DOMAINES_HAL_THESE     = 'these-modification-domaines-hal-these';
    public const THESE_DONNER_RESULTAT             = 'these-donner-resultat';

}