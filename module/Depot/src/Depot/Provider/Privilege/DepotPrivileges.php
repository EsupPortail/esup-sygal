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
    public const THESE_SAISIE_CORREC_AUTORISEE_FORCEE = 'these-saisie-correc-autorisee-forcee';
    public const THESE_CORREC_AUTORISEE_ACCORDER_SURSIS = 'these-accorder-sursis-correction';
    public const THESE_CONSULTATION_CORREC_AUTORISEE_INFORMATIONS = 'these-consultation-correction-autorisee-informations';

    /**
     * Page de couverture
     */
    public const THESE_CONSULTATION_PAGE_COUVERTURE = 'these-consultation-page-couverture';

    /**
     * Dépôt de fichiers liés à une thèse
     */
    public const THESE_DEPOT_VERSION_INITIALE = 'these-depot-version-initiale';
    public const THESE_DEPOT_VERSION_CORRIGEE              = 'these-depot-version-corrigee';
    public const THESE_CONSULTATION_DEPOT                  = 'these-consultation-depot';
    public const THESE_TELECHARGEMENT_FICHIER              = 'these-telechargement-fichier';
    public const THESE_FICHIER_DIVERS_TELEVERSER           = 'these-fichier-divers-televerser';
    public const THESE_FICHIER_DIVERS_CONSULTER            = 'these-fichier-divers-consulter';

    /**
     * Dépôt de fichiers liés à une HDR
     */
    public const HDR_TELECHARGEMENT_FICHIER              = 'hdr-telechargement-fichier';
    public const HDR_FICHIER_DIVERS_TELEVERSER           = 'hdr-fichier-divers-televerser';
    public const HDR_FICHIER_DIVERS_CONSULTER            = 'hdr-fichier-divers-consulter';

    /**
     * Attestations
     */
    public const THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE = 'these-saisie-attestations-version-initiale';
    public const THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE = 'these-saisie-attestations-version-corrigee';

    /**
     * Diffusion
     */
    public const THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE = 'these-saisie-autorisation-diffusion-version-initiale';
    public const THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE = 'these-saisie-autorisation-diffusion-version-corrigee';
    public const THESE_EDITION_CONVENTION_MEL              = 'these-edition-convention-mel';

    /**
     * Description ("Signalement")
     */
    public const THESE_CONSULTATION_DESCRIPTION            = 'these-consultation-description';
    public const THESE_SAISIE_DESCRIPTION_VERSION_INITIALE = 'these-saisie-description-version-initiale';
    public const THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE = 'these-saisie-description-version-corrigee';

    /**
     * Archivage
     */
    public const THESE_CONSULTATION_ARCHIVAGE              = 'these-consultation-archivage';
    public const THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE = 'these-saisie-conformite-version-archivage-initiale';
    public const THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE = 'these-saisie-conformite-version-archivage-corrigee';

    /**
     * Rendez-vous BU
     */
    public const THESE_CONSULTATION_RDV_BU                 = 'these-consultation-rdv-bu';
    public const THESE_SAISIE_RDV_BU                       = 'these-saisie-rdv-bu';
    public const THESE_SAISIE_MOT_CLE_RAMEAU               = 'these-saisie-mot-cle-rameau';

    /**
     * Remise de la version corigée
     */
    public const THESE_CONSULTATION_VERSION_PAPIER_CORRIGEE  = 'these-consultation-version-papier-corrigee';

    /**  **************************************************************************************************************/

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_DESCRIPTION_($correctionAttendue)
    {
        return $correctionAttendue ?
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