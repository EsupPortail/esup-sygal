<?php

namespace Application\Provider\Privilege;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
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

    /**
     * Dépôt
     */
    const THESE_DEPOT_VERSION_INITIALE              = 'these-depot-version-initiale';
    const THESE_DEPOT_VERSION_CORRIGEE              = 'these-depot-version-corrigee';
    const THESE_CONSULTATION_DEPOT                  = 'these-consultation-depot';
    const THESE_TELECHARGEMENT_FICHIER              = 'these-telechargement-fichier';
    const FICHIER_DIVERS_TELEVERSER                 = 'fichier-divers-televerser';
    const FICHIER_DIVERS_CONSULTER                  = 'fichier-divers-consulter';

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


    /** NOUVEAU PRIVILIEGES DE LA POC *********************************************************************************/

    const THESE_CONSULTATION_TOUTES_THESES          = 'these-consultation-de-toutes-les-theses';
    const THESE_CONSULTATION_SES_THESES             = 'these-consultation-de-ses-theses';
    const THESE_MODIFICATION_TOUTES_THESES          = 'these-modification-de-toutes-les-theses';
    const THESE_MODIFICATION_SES_THESES             = 'these-modification-de-ses-theses';

    /**  **************************************************************************************************************/

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_DESCRIPTION_($correctionAttendue)
    {
        return (bool) $correctionAttendue ?
            ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE :
            ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;
    }

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_ATTESTATIONS_($correctionAttendue)
    {
        return (bool) $correctionAttendue ?
            ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE :
            ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;
    }

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_AUTORISATION_DIFFUSION_($correctionAttendue)
    {
        return (bool) $correctionAttendue ?
            ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE :
            ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;
    }

    /**
     * @param bool $correctionAttendue
     * @return string
     */
    static public function THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_($correctionAttendue)
    {
        return $correctionAttendue ?
            ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE :
            ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;
    }

    /**
     * Retourne le privilège correspondant au téléversement/suppression de fichier répondant aux critères spécifiés.
     *
     * @param NatureFichier  $nature
     * @param VersionFichier $versionFichier
     * @return string
     */
    public static function privilegeDeposerFor(NatureFichier $nature, VersionFichier $versionFichier)
    {
        switch ($nature->getCode()) {
            case NatureFichier::CODE_PV_SOUTENANCE:
            case NatureFichier::CODE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_DEMANDE_CONFIDENT:
            case NatureFichier::CODE_PROLONG_CONFIDENT:
            case NatureFichier::CODE_CONV_MISE_EN_LIGNE:
            case NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE:
                return static::FICHIER_DIVERS_TELEVERSER;
        }

        return $versionFichier->estVersionCorrigee() ?
            ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE :
            ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;
    }

    /**
     * Retourne le privilège correspondant à la consultation des fichiers déposés répondant aux critères spécifiés.
     *
     * @param NatureFichier $nature
     * @return string
     */
    public static function privilegeConsulterDepotFor(NatureFichier $nature)
    {
        if ($nature->getCode() === NatureFichier::CODE_PV_SOUTENANCE) {
            return static::FICHIER_DIVERS_CONSULTER;
        }
        if ($nature->getCode() === NatureFichier::CODE_RAPPORT_SOUTENANCE) {
            return static::FICHIER_DIVERS_CONSULTER;
        }

        return ThesePrivileges::THESE_TELECHARGEMENT_FICHIER;
    }
}