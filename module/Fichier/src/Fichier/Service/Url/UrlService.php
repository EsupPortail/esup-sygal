<?php

namespace Fichier\Service\Url;

use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use LogicException;
use RuntimeException;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class UrlService
{
    use FichierStorageServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    private array $parameters = [];

    public function setParams(array $params): void
    {
        $this->parameters = array_merge($this->parameters, $params);
    }

    /**
     * Retourne le chemin absolu sur disque du logo (fichier) de la COMUE éventuelle.
     *
     * @noinspection
     */
    public function getLogoEtablissementComue(): ?string
    {
        $comue = $this->etablissementService->fetchEtablissementComue();
        if ($comue === null) {
            // NB : il n'y a pas forcément de COMUE
            return null;
        }

        try {
            return $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le chemin du logo de la COMUE",
                null,
                $e
            );
        }
    }

    /**
     * Retourne le chemin absolu sur disque du logo (fichier) du Collège des Ecoles Doctorales éventuel.
     *
     * @noinspection
     */
    public function getLogoEtablissementCed(): ?string
    {
        $ced = $this->etablissementService->fetchEtablissementCed();
        if ($ced === null) {
            // NB : il n'y a pas forcément de CED
            return null;
        }

        try {
            return $this->fichierStorageService->getFileForLogoStructure($ced->getStructure());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le chemin du logo du Collège des Ecoles Doctorales",
                null,
                $e
            );
        }
    }

    /**
     * Retourne le chemin absolu sur disque du logo (fichier) de l'établissement d'inscription pour la thèse spécifiée au préalable.
     *
     * @noinspection
     */
    public function getLogoEtablissementInscr(): string
    {
        /** @var \These\Entity\Db\These $these */
        $these = $this->getRequiredParam('these');

        try {
            return $this->fichierStorageService->getFileForLogoStructure($these->getEtablissement()->getStructure());
        } catch (StorageAdapterException $e) {
            throw new RuntimeException(
                "Impossible d'obtenir le chemin du logo de l'établissement d'inscription pour la thèse " . $these->getId(),
                null,
                $e
            );
        }
    }

    public function getRequiredParam(string $name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new LogicException("Le paramètre '$name' n'a pas été fourni");
        }

        return $this->parameters[$name];
    }
}