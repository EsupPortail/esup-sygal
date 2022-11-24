<?php

namespace Fichier\Service\Fichier;

use Application\Service\BaseService;
use Fichier\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Exception;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\FileUtils;
use Fichier\Filter\AbstractNomFichierFormatter;
use Fichier\Filter\NomFichierFormatter;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Laminas\Filter\FilterInterface;
use UnicaenApp\Exception\RuntimeException;
use ZipArchive;

class FichierService extends BaseService
{
    use FichierStorageServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use ValiditeFichierServiceAwareTrait;

    /**
     * @var AbstractNomFichierFormatter
     */
    protected $nomFichierFormatter;

    /**
     * @param AbstractNomFichierFormatter $nomFichierFormatter
     */
    public function setNomFichierFormatter(AbstractNomFichierFormatter $nomFichierFormatter)
    {
        $this->nomFichierFormatter = $nomFichierFormatter;
    }

    /**
     * FichierService constructor.
     */
    public function __construct()
    {
        $this->nomFichierFormatter = new NomFichierFormatter();
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Fichier::class);

        return $repo;
    }

    /**
     * Instancie des fichiers, à partir des données résultant d'un upload de fichiers.
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
     * @param array $uploadResult Données résultant d'un upload de fichiers
     * @param string|NatureFichier $nature Nature de fichier, ou son code
     * @param string|VersionFichier|null $version Version de fichier, ou son code.
     *                                                 Si null, ce sera VersionFichier::CODE_ORIG
     * @return Fichier[] Fichiers instanciés
     */
    public function createFichiersFromUpload(array $uploadResult, $nature, $version = null): array
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
                ['code' => $nature ?: NatureFichier::CODE_COMMUNS]
            );
        }

        // normalisation au cas où il n'y a qu'un fichier
        if (isset($files['name'])) {
            $files = [$files];
        }

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
                ->setPath($path); // non mappé en BDD mais utilisé dans {@link moveUploadedFileForFichier}

            $nom = $this->nomFichierFormatter->filter($fichier); // en dernier car le formatter exploite des propriétés de l'entité
            $fichier->setNom($nom);

            $fichiers[] = $fichier;
        }

        return $fichiers;
    }

    /**
     * Enregistre en base de données les Fichiers spécifiés, et enregsitre les fichiers physiques associés.
     *
     * @param Fichier[] $fichiers
     */
    public function saveFichiers(array $fichiers)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                $this->entityManager->persist($fichier);
                $this->entityManager->flush($fichier);

//                $this->moveUploadedFileForFichier($fichier);
                $this->fichierStorageService->saveFileForFichier($fichier->getPath(), $fichier);
            }
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de l'enregistrement des fichiers, rollback!", 0, $e);
        }
    }

    /**
     * Supprime définitivement des fichiers.
     *
     * @param Fichier[] $fichiers
     */
    public function supprimerFichiers(array $fichiers, bool $throwExceptionOnFileError = false)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                if ($fichier->getIdPermanent() !== null) {
                    throw new Exception(sprintf(
                        "Interdit de supprimer un fichier possédant un id permanent (en l'occurence : '%s')",
                        $fichier->getIdPermanent()
                    ));
                }

                $this->entityManager->remove($fichier);
                $this->entityManager->flush($fichier);
            }
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur survenue lors de la suppression des Fichiers en bdd, rollback!", 0, $e);
        }

        // suppression des fichiers physiques
        $notDeletedFiles = [];
        foreach ($fichiers as $fichier) {
            try {
                $this->fichierStorageService->deleteFileForFichier($fichier);
            } catch (StorageAdapterException $e) {
                $notDeletedFiles[] = sprintf("%s/%s (%s)", $e->getDirPath(), $e->getFileName(), $e->getMessage());
            }
        }
        if ($throwExceptionOnFileError && $notDeletedFiles) {
            throw new RuntimeException(
                "Les fichiers suivants n'ont pas pu être supprimés du storage : " . implode(', ', $notDeletedFiles));
        }
    }

    /**
     * Compression des fichiers physiques de plusieurs Fichier en une archive .zip puis création d'un Fichier temporaire
     * "pointant" vers cette archive.
     *
     * NB: c'est une entité Fichier qui est retournée pour une raison de praticité, elle n'a pas du tout vocation à
     * être persistée.
     *
     * @param \Fichier\Entity\FichierArchivable[] $fichiersArchivables
     * @param string $zipFileName
     * @return Fichier
     * @throws FichierServiceException
     */
    public function compresserFichiers(array $fichiersArchivables, string $zipFileName = "archive.zip"): Fichier
    {
        $archiveFilepath = sys_get_temp_dir() . '/' . uniqid('sygal_archive_') . '.zip';

        $archive = new ZipArchive();
        if ($archive->open($archiveFilepath, ZipArchive::CREATE) !== TRUE) {
            throw new FichierServiceException("Impossible de créer le fichier " . $archiveFilepath);
        }

        foreach ($fichiersArchivables as $fichierArchivable) {
            // NB : le chemin du fichier à archiver est soit celui du FichierArchivable (spécifié à la main),
            // soit celui du Fichier original calculé comme d'hab :
            if ($fichierArchivable->getFilePath()) {
                $filePath = $fichierArchivable->getFilePath();
                if (! is_readable($filePath)) {
                    $message = "Impossible d'ajouter le fichier suivant à l'archive '$archiveFilepath' car il n'est pas lisible : " . $filePath;
                    error_log($message);
                    throw new FichierServiceException($message);
                }
            } else {
                try {
                    $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
                    $filePath = $this->fichierStorageService->getFileForFichier($fichierArchivable->getFichier());
                } catch (StorageAdapterException $e) {
                    $message = "Impossible d'ajouter le fichier suivant à l'archive '$archiveFilepath' : " . $fichierArchivable->getFichier();
                    error_log($message);
                    throw new FichierServiceException($message, null, $e);
                }
            }

            $filePathInArchive = $fichierArchivable->getFilePathInArchive();
            $archive->addFile($filePath, $filePathInArchive);
        }
        $archive->close();

        $fichier = Fichier::fromFilepath($archiveFilepath);
        $fichier->setNom($zipFileName);

        return $fichier;
    }

    /**
     * Téléchargement d'un Fichier.
     *
     * @param Fichier $fichier
     */
    public function telechargerFichier(Fichier $fichier)
    {
        $contenu     = $fichier->getContenu();
        $content     = is_resource($contenu) ? stream_get_contents($contenu) : $contenu;
        $contentType = $fichier->getTypeMime() ?: 'application/octet-stream';

        FileUtils::downloadFileFromContent($content, $fichier->getNom(), $contentType);
    }
}