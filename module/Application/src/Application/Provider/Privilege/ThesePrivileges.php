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
     * Recherche
     */
    const THESE_RECHERCHE                           = 'these-recherche';
    const THESE_EXPORT_CSV                          = 'these-export-csv';

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
    const THESE_SAISIE_ATTESTATIONS                 = 'these-saisie-attestations';

    /**
     * Diffusion
     */
    const THESE_SAISIE_AUTORISATION_DIFFUSION       = 'these-saisie-autorisation-diffusion';
    const THESE_EDITION_CONVENTION_MEL              = 'these-edition-convention-mel';

    /**
     * Description ("Signalement")
     */
    const THESE_CONSULTATION_DESCRIPTION            = 'these-consultation-description';
    const THESE_SAISIE_DESCRIPTION                  = 'these-saisie-description';

    /**
     * Archivage
     */
    const THESE_CONSULTATION_ARCHIVAGE              = 'these-consultation-archivage';
    const THESE_SAISIE_CONFORMITE_ARCHIVAGE         = 'these-saisie-conformite-archivage';

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