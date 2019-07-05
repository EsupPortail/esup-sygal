<?php

namespace Application\Service\Fichier;

use Application\Entity\Db\Fichier;
use Application\Service\BaseService;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use UnicaenApp\Exception\RuntimeException;

class FichierService extends BaseService
{
    use FileServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Fichier::class);

        return $repo;
    }

    /**
     * Retourne le contenu d'un Fichier sous la forme d'une chaîne de caractères.
     *
     * @param Fichier $fichier
     * @return string
     */
    public function fetchContenuFichier(Fichier $fichier)
    {
        $filePath = $this->computeDestinationFilePathForFichier($fichier);

        if (! is_readable($filePath)) {
            throw new RuntimeException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        $contenuFichier = file_get_contents($filePath);

        return $contenuFichier;
    }

    /**
     * Retourne le chemin sur le disque (du serveur) du dossier parent du fichier physique associé à un Fichier.
     *
     * @param Fichier $fichier      Entité Fichier dont on veut connaître le chemin du fichier physique associé
     *                                   stocké sur disque
     * @return string
     */
    public function computeDestinationDirectoryPathForFichier(Fichier $fichier)
    {
        return $this->fileService->prependUploadRootDirToRelativePath(strtolower($fichier->getNature()->getCode()));
    }

    /**
     * Retourne le chemin sur le disque (du serveur) du fichier physique associé à un Fichier.
     *
     * @param Fichier $fichier      Entité Fichier dont on veut connaître le chemin du fichier physique associé
     *                                   stocké sur disque
     * @return string
     */
    public function computeDestinationFilePathForFichier(Fichier $fichier)
    {
        return $this->computeDestinationDirectoryPathForFichier($fichier) . '/' . $fichier->getNom();
    }

    /**
     * Création si besoin du dossier destination du Fichier spécifié.
     *
     * @param Fichier $fichier
     */
    public function createDestinationDirectoryPathForFichier(Fichier $fichier)
    {
        $parentDir = $this->computeDestinationDirectoryPathForFichier($fichier);
        $this->fileService->createWritableDirectory($parentDir);
    }

    /**
     * @param Fichier $fichier
     * @param string  $fromPath
     */
    public function moveUploadedFileForFichier(Fichier $fichier, $fromPath)
    {
        // création si besoin du dossier destination
        $this->createDestinationDirectoryPathForFichier($fichier);

        $newPath = $this->computeDestinationFilePathForFichier($fichier);
        $res = move_uploaded_file($fromPath, $newPath);

        if ($res === false) {
            throw new RuntimeException("Impossible de déplacer le fichier temporaire uploadé de $fromPath vers $newPath");
        }
    }
}