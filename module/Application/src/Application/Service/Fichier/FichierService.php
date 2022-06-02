<?php

namespace Application\Service\Fichier;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Filter\AbstractNomFichierFormatter;
use Application\Filter\NomFichierFormatter;
use Application\Service\BaseService;
use Application\Service\Fichier\Exception\FichierServiceException;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\ValiditeFichier\ValiditeFichierServiceAwareTrait;
use Application\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Exception;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Filter\FilterInterface;
use ZipArchive;

class FichierService extends BaseService
{
    use FileServiceAwareTrait;
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
    public function getRepository()
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
     * @param FilterInterface|null       $nomFichierFormatter
     * @return Fichier[] Fichiers instanciés
     */
    public function createFichiersFromUpload(array $uploadResult, $nature, $version = null, FilterInterface $nomFichierFormatter = null)
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

        if ($nomFichierFormatter === null) {
            $nomFichierFormatter = new NomFichierFormatter();
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
     * Enregistre en base de données les Fichiers spécifiés, et déplace les fichiers physiques associés au bon endroit.
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
                $this->moveUploadedFileForFichier($fichier);
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
        $filePaths = [];
        $this->entityManager->beginTransaction();
        try {
            foreach ($fichiers as $fichier) {
                if ($fichier->getIdPermanent() !== null) {
                    throw new Exception(sprintf(
                        "Interdit de supprimer un fichier possédant un id permanent (en l'occurence : '%s')",
                        $fichier->getIdPermanent()
                    ));
                }
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
            $success = file_exists($filePath) && unlink($filePath);
            if ($success === false) {
                $notDeletedFiles[] = $filePath;
            }
        }
        if ($throwExceptionOnFileError && $notDeletedFiles) {
            throw new RuntimeException(
                "Les fichiers suivants n'ont pas pu être supprimés sur le disque : " . implode(', ', $notDeletedFiles));
        }
    }

    /**
     * Retourne le contenu d'un Fichier sous la forme d'une chaîne de caractères.
     *
     * @param Fichier $fichier
     * @return string
     * @throws \Application\Service\Fichier\Exception\FichierServiceException
     */
    public function fetchContenuFichier(Fichier $fichier): string
    {
        $filePath = $this->computeDestinationFilePathForFichier($fichier);

        if (!is_readable($filePath)) {
            throw new FichierServiceException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        return file_get_contents($filePath);
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
    public function computeDestinationFilePathForFichier(Fichier $fichier): string
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
     */
    public function moveUploadedFileForFichier(Fichier $fichier)
    {
        $fromPath = $fichier->getPath();

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

        $fichier->setPath($newPath);
    }

    /**
     * Compression des fichiers physiques de plusieurs Fichier en une archive .zip puis création d'un Fichier temporaire
     * "pointant" vers cette archive.
     *
     * NB: c'est une entité Fichier qui est retournée pour une raison de praticité, elle n'a pas du tout vocation à
     * être persistée.
     *
     * @param Fichier[] $fichiers
     * @param string $zipFileName
     * @return Fichier
     * @throws FichierServiceException
     */
    public function compresserFichiers(array $fichiers, string $zipFileName = "archive.zip"): Fichier
    {
        $archiveFilepath = sys_get_temp_dir() . '/' . uniqid('sygal_archive_') . '.zip';

        $archive = new ZipArchive();
        if ($archive->open($archiveFilepath, ZipArchive::CREATE) !== TRUE) {
            throw new FichierServiceException("Impossible de créer le fichier " . $archiveFilepath);
        }
        foreach ($fichiers as $fichier) {
            $filePath = $this->computeDestinationFilePathForFichier($fichier);
            if (! is_readable($filePath)) {
                $message = "Impossible d'ajouter le fichier suivant à l'archive '$archiveFilepath' car il n'est pas lisible : " . $filePath;
                error_log($message);
                throw new FichierServiceException($message);
            }
            $filePathInArchive = $fichier->getPath();
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

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $fichier->getNom() . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $content;
        exit;
    }
}