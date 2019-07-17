<?php

namespace Application\Service\Fichier;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Filter\NomFichierFormatter;
use Application\Service\BaseService;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use UnicaenApp\Exception\RuntimeException;

class FichierService extends BaseService
{
    use FileServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
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
     * Crée des fichiers en base de données, à partir des données résultant d'un upload de fichiers.
     *
     * Formats attendus pour les données d'upload :
     * <pre>
     * [
     *     'files' => [
     *         'tmp_name' => 'xxxxxx',
     *         'name' => 'Mon fichier.odt',
     *         'type' => 'application/pdf',
     *         'size' => '12345',
     *     ]
     * ]
     * </pre>
     * ou
     * <pre>
     * [
     *     'files' => [
     *          [
     *              'tmp_name' => 'xxxxxx',
     *              'name' => 'Mon fichier.pdf',
     *              'type' => 'application/pdf',
     *              'size' => '12345',
     *          ],
     *          [
     *              'tmp_name' => 'yyyyyyy',
     *              'name' => 'Mon second fichier.pdf',
     *              'type' => 'application/pdf',
     *              'size' => '65412',
     *          ],
     *     ]
     * ]
     * </pre>
     *
     * @param array                      $uploadResult Données résultant d'un upload de fichiers
     * @param string|NatureFichier       $nature       Nature de fichier, ou son code
     * @param string|VersionFichier|null $version      Version de fichier, ou son code.
     *                                                 Si null, ce sera VersionFichier::CODE_ORIG
     * @return Fichier[] Fichiers créés
     */
    public function createFichiersFromUpload(array $uploadResult, $nature, $version = null)
    {
        $fichiers = [];
        $files = $uploadResult['files'];

        if (!$version instanceof VersionFichier) {
            $version = $this->versionFichierService->getRepository()->findOneBy(
                ['code' => $version ?: VersionFichier::CODE_ORIG]
            );
        }
        if (!$nature instanceof NatureFichier) {
            $nature = $this->natureFichierService->getRepository()->findOneBy(
                ['code' => $nature ?: NatureFichier::CODE_DIVERS]
            );
        }

        // normalisation au cas où il n'y a qu'un fichier
        if (isset($files['name'])) {
            $files = [$files];
        }

        $nomFichierFormatter = new NomFichierFormatter();

        foreach ((array)$files as $file) {
            $path = $file['tmp_name'];
            $nomFichier = $file['name'];
            $typeFichier = $file['type'];
            $tailleFichier = $file['size'];

            if (!is_uploaded_file($path)) {
                throw new RuntimeException("Possible file upload attack: " . $path);
            }

            $fichier = new Fichier();
            $fichier
                ->setNature($nature)
                ->setVersion($version)
                ->setTypeMime($typeFichier)
                ->setNomOriginal($nomFichier)
                ->setTaille($tailleFichier)
                ->setNom($nomFichierFormatter->filter($fichier));

            $this->moveUploadedFileForFichier($fichier, $path);

            $this->entityManager->persist($fichier);
            try {
                $this->entityManager->flush($fichier);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du Fichier", null, $e);
            }

            $fichiers[] = $fichier;
        }

        return $fichiers;
    }

    /**
     * Supprime définitivement des fichiers.
     *
     * @param Fichier[] $fichiers
     */
    public function supprimerFichiers(array $fichiers)
    {
        $filePaths = [];
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                $filePaths[] = $this->computeDestinationFilePathForFichier($fichier);
                $this->entityManager->remove($fichier);
                $this->entityManager->flush($fichier);
            }
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des Fichiers en bdd, rollback!", 0, $e);
        }

        // suppression des fichiers physiques sur le disque
        $notDeletedFiles = [];
        foreach ($filePaths as $filePath) {
            $success = unlink($filePath);
            if ($success === false) {
                $notDeletedFiles[] = $filePath;
            }
        }
        if ($notDeletedFiles) {
            throw new RuntimeException(
                "Les fichiers suivants n'ont pas pu être supprimés sur le disque : " . implode(', ', $notDeletedFiles));
        }
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

        if (!is_readable($filePath)) {
            throw new RuntimeException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        $contenuFichier = file_get_contents($filePath);

        return $contenuFichier;
    }

    /**
     * Retourne le chemin sur le disque (du serveur) du dossier parent du fichier physique associé à un Fichier.
     *
     * @param Fichier $fichier           Entité Fichier dont on veut connaître le chemin du fichier physique associé
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
     * @param Fichier $fichier Entité Fichier dont on veut connaître le chemin du fichier physique associé
     *                         stocké sur disque
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
        if (file_exists($newPath)) {
            throw new RuntimeException("Impossible de déplacer le fichier temporaire uploadé car $newPath existe déjà");
        }

        $res = move_uploaded_file($fromPath, $newPath);
        if ($res === false) {
            throw new RuntimeException("Erreur lors du déplacement du fichier temporaire uploadé de $fromPath vers $newPath");
        }
    }
}