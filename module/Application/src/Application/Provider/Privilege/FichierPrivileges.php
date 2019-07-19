<?php

namespace Application\Provider\Privilege;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations sur les fichiers *NE CONCERNANT PAS UNE THÈSE*.
 *
 * @see ThesePrivileges Pour ce qui concerne les thèses.
 */
class FichierPrivileges extends Privileges
{
    /**
     * Dépôt de fichiers dits "divers" (ex: fichiers pour les pages d'informations).
     */
    const FICHIER_DIVERS_TELEVERSER = 'fichier-divers-televerser';
    const FICHIER_DIVERS_TELECHARGER = 'fichier-divers-telecharger';

    /**
     * Dépôt de fichiers "communs" (ex: modèle d'avenant à la convention de MEL).
     */
    const FICHIER_COMMUN_TELEVERSER = 'fichier-commun-televerser';
    const FICHIER_COMMUN_TELECHARGER = 'fichier-commun-telecharger';

    /**
     * Retourne le privilège correspondant au téléversement/suppression de fichier
     * en fonction de la nature et de la version de fichier.
     *
     * @param NatureFichier       $nature
     * @param VersionFichier|null $versionFichier
     * @return string
     */
    public static function privilegeTeleverserFor(NatureFichier $nature, VersionFichier $versionFichier = null)
    {
        switch ($nature->getCode()) {
            case NatureFichier::CODE_PV_SOUTENANCE:
            case NatureFichier::CODE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_DEMANDE_CONFIDENT:
            case NatureFichier::CODE_PROLONG_CONFIDENT:
            case NatureFichier::CODE_CONV_MISE_EN_LIGNE:
            case NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE:
                //
                return ThesePrivileges::THESE_FICHIER_DIVERS_TELEVERSER;

            case NatureFichier::CODE_THESE_PDF:
            case NatureFichier::CODE_FICHIER_NON_PDF:
                //
                return $versionFichier !== null && $versionFichier->estVersionCorrigee() ?
                    ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE :
                    ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;

            case NatureFichier::CODE_DIVERS:
                //
                return self::FICHIER_DIVERS_TELEVERSER;

            case NatureFichier::CODE_COMMUNS:
                //
                return self::FICHIER_COMMUN_TELEVERSER;

            default:
                //
                throw new RuntimeException("Cas non implémenté");
        }
    }

    /**
     * Retourne le privilège correspondant à l'opération de téléchargement de fichier
     * en fonction de la nature de fichier.
     *
     * @param NatureFichier       $nature
     * @param VersionFichier|null $versionFichier
     * @return string
     */
    public static function privilegeTelechargerFor(NatureFichier $nature, VersionFichier $versionFichier = null)
    {
        switch ($nature->getCode()) {
            case NatureFichier::CODE_PV_SOUTENANCE:
            case NatureFichier::CODE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_DEMANDE_CONFIDENT:
            case NatureFichier::CODE_PROLONG_CONFIDENT:
            case NatureFichier::CODE_CONV_MISE_EN_LIGNE:
            case NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE:
                //
                return ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER;

            case NatureFichier::CODE_THESE_PDF:
            case NatureFichier::CODE_FICHIER_NON_PDF:
                //
                return $versionFichier !== null && $versionFichier->estVersionCorrigee() ?
                    ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE :
                    ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;

            case NatureFichier::CODE_DIVERS:
                //
                return self::FICHIER_DIVERS_TELECHARGER;

            case NatureFichier::CODE_COMMUNS:
                //
                return self::FICHIER_COMMUN_TELECHARGER;

            default:
                //
                throw new RuntimeException("Cas non implémenté");
        }
    }
}