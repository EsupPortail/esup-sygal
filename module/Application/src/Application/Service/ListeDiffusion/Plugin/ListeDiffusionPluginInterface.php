<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Service\ListeDiffusion\ListeDiffusionParser;

interface ListeDiffusionPluginInterface
{
    /**
     * @param string[] $config
     */
    public function setConfig(array $config);

    /**
     * @param string $liste
     */
    public function setListe($liste);

    /**
     * @return bool
     */
    public function canHandleListe();

    /**
     *
     */
    public function init();

    /**
     * @return string[] [mail => nom individu]
     */
    public function getIndividusAvecAdresse();

    /**
     * @return string[] [id individu => nom individu]
     */
    public function getIndividusSansAdresse();

//    /**
//     * @return string[]
//     */
//    public function fetchListesDiffusion();

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createMemberIncludeFileContent();

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent();

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName($prefix);
}