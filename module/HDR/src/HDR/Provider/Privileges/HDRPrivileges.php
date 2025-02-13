<?php

namespace HDR\Provider\Privileges;

use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations concernant les hdr.
 */
class HDRPrivileges extends Privileges
{
    /**
     * Tout faire !
     */
    const HDR_TOUT_FAIRE                          = 'hdr-tout-faire';

    /**
     * Recherche
     */
    const HDR_RECHERCHE                           = 'hdr-recherche';
    const HDR_EXPORT_CSV                          = 'hdr-export-csv';

    /**
     * Fiche ("HDR")
     */
    const HDR_CONSULTATION_FICHE                  = 'hdr-consultation-fiche';

    /** NOUVEAU PRIVILIEGES DE LA POC *********************************************************************************/

    const HDR_CONSULTATION_TOUTES_HDRS          = 'hdr-consultation-de-toutes-les-hdr';
    const HDR_CONSULTATION_SES_HDRS             = 'hdr-consultation-de-ses-hdr';
    const HDR_MODIFICATION_TOUTES_HDRS          = 'hdr-modification-de-toutes-les-hdr';
    const HDR_MODIFICATION_SES_HDRS             = 'hdr-modification-de-ses-hdr';
    const HDR_DONNER_RESULTAT             = 'hdr-donner-resultat';
}