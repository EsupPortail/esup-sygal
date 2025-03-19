<?php

namespace Depot\Provider\Privilege;

use Fichier\Entity\Db\VersionFichier;
use UnicaenPrivilege\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations concernant les thèses.
 */
class DepotPrivileges extends Privileges
{
    /**
     * Fiche ("Thèse")
     */
    const THESE_SAISIE_CORREC_AUTORISEE_FORCEE      = 'these-saisie-correc-autorisee-forcee';
    const THESE_CORREC_AUTORISEE_ACCORDER_SURSIS    = 'these-accorder-sursis-correction';
    const THESE_CONSULTATION_CORREC_AUTORISEE_INFORMATIONS    = 'these-consultation-correction-autorisee-informations';

    /**
     * Page de couverture
     */
    const THESE_CONSULTATION_PAGE_COUVERTURE        = 'these-consultation-page-couverture';

    /**
     * Dépôt de fichiers liés à une thèse
     */
    const THESE_DEPOT_VERSION_INITIALE              = 'these-depot-version-initiale';
    const THESE_DEPOT_VERSION_CORRIGEE              = 'these-depot-version-corrigee';
    const THESE_CONSULTATION_DEPOT                  = 'these-consultation-depot';
    const THESE_TELECHARGEMENT_FICHIER              = 'these-telechargement-fichier';
    const THESE_FICHIER_DIVERS_TELEVERSER           = 'these-fichier-divers-televerser';
    const THESE_FICHIER_DIVERS_CONSULTER            = 'these-fichier-divers-consulter';

    /**
     * Attestations
     */
    const THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE = 'these-saisie-attestations-version-initiale';
    const THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE = 'these-saisie-attestations-version-corrigee';

    /**
     * Diffusion
     */
    const THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE = 'these-saisie-autorisation-diffusion-version-initiale';
    const THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE = 'these-saisie-autorisation-diffusion-version-corrigee';
    const THESE_EDITION_CONVENTION_MEL              = 'these-edition-convention-mel';

    /**
     * Description ("Signalement")
     */
    const THESE_CONSULTATION_DESCRIPTION            = 'these-consultation-description';
    const THESE_SAISIE_DESCRIPTION_VERSION_INITIALE = 'these-saisie-description-version-initiale';
    const THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE = 'these-saisie-description-version-corrigee';

    /**
     * Archivage
     */
    const THESE_CONSULTATION_ARCHIVAGE              = 'these-consultation-archivage';
    const THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE = 'these-saisie-conformite-version-archivage-initiale';
    const THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE = 'these-saisie-conformite-version-archivage-corrigee';

    /**
     * Rendez-vous BU
     */
    const THESE_CONSULTATION_RDV_BU                 = 'these-consultation-rdv-bu';
    const THESE_SAISIE_RDV_BU                       = 'these-saisie-rdv-bu';
    const THESE_SAISIE_MOT_CLE_RAMEAU               = 'these-saisie-mot-cle-rameau';

    /**
     * Remise de la version corigée
     */
    const THESE_CONSULTATION_VERSION_PAPIER_CORRIGEE  = 'these-consultation-version-papier-corrigee';

    /**  **************************************************************************************************************/

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_DESCRIPTION_($correctionAttendue)
    {
        return (bool) $correctionAttendue ?
            DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE :
            DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;
    }

    /**
     * @param VersionFichier $version
     * @return string
     */
    static public function THESE_SAISIE_ATTESTATIONS_(VersionFichier $version)
    {
        return $version->estVersionCorrigee() ?
            DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE :
            DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;
    }

    /**
     * @param VersionFichier $version
     * @return string
     */
    static public function THESE_SAISIE_AUTORISATION_DIFFUSION_(VersionFichier $version)
    {
        return $version->estVersionCorrigee() ?
            DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE :
            DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;
    }

    /**
     * @param VersionFichier $version
     * @return string
     */
    static public function THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_(VersionFichier $version)
    {
        return $version->estVersionCorrigee() ?
            DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE :
            DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;
    }
}