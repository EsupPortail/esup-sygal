<?php

namespace Fichier\Provider\Privilege;

use Depot\Provider\Privilege\DepotPrivileges;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Privilèges pour les opérations sur les fichiers *NE CONCERNANT PAS UNE THÈSE*.
 *
 * @see DepotPrivileges Pour ce qui concerne les thèses.
 */
class FichierPrivileges extends Privileges
{
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
    public static function privilegeTeleverserFor(NatureFichier $nature, ?VersionFichier $versionFichier = null): string
    {
        switch (true) {
            case in_array($nature->getCode(), NatureFichier::CODES_FICHIERS_DIVERS):
                //
                return DepotPrivileges::THESE_FICHIER_DIVERS_TELEVERSER;

            case in_array($nature->getCode(), [
                NatureFichier::CODE_THESE_PDF,
                NatureFichier::CODE_FICHIER_NON_PDF,
            ]):
                //
                return $versionFichier !== null && $versionFichier->estVersionCorrigee() ?
                    DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE :
                    DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;

            case $nature->getCode() === NatureFichier::CODE_COMMUNS:
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
        switch (true) {
            case in_array($nature->getCode(), NatureFichier::CODES_FICHIERS_DIVERS):
                //
                return DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER;

            case in_array($nature->getCode(), [
                NatureFichier::CODE_THESE_PDF,
                NatureFichier::CODE_FICHIER_NON_PDF,
            ]):
                return DepotPrivileges::THESE_TELECHARGEMENT_FICHIER;

            case $nature->getCode() === NatureFichier::CODE_COMMUNS:
                //
                return self::FICHIER_COMMUN_TELECHARGER;

            default:
                //
                throw new RuntimeException("Cas non implémenté");
        }
    }
}