<?php

namespace Fichier\Service\Fichier;

use Fichier\Entity\Db\Fichier;
use Fichier\Exporter\PageFichierIntrouvablePdfExporterTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\Storage\Adapter\StorageAdapterInterface;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use Structure\Entity\Db\StructureInterface;
use UnicaenApp\Util;

class FichierStorageService
{
    use PageFichierIntrouvablePdfExporterTrait;

    const DIR_ETAB = 'Etab';
    const DIR_ED = 'ED';
    const DIR_UR = 'UR';

    private StorageAdapterInterface $storageAdapter;

    /**
     * @var bool Active ou non la génération d'un PDF de substitution en cas de fichier introuvable.
     */
    private bool $genererFichierSubstitutionSiIntrouvable = true;

    public function setStorageAdapter(StorageAdapterInterface $storageAdapter): void
    {
        $this->storageAdapter = $storageAdapter;
    }

    /**
     * @param bool $genererFichierSubstitutionSiIntrouvable
     */
    public function setGenererFichierSubstitutionSiIntrouvable(?bool $genererFichierSubstitutionSiIntrouvable = true)
    {
        $this->genererFichierSubstitutionSiIntrouvable = $genererFichierSubstitutionSiIntrouvable;
    }


    /********************************** Fichiers standards *************************************/

    /**
     * Retourne le nom normalisé du répertoire parent du fichier physique associé à un Fichier.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string
     */
    private function computeDirectoryNameForFichier(Fichier $fichier): string
    {
        return strtolower($fichier->getNature()->getCode());
    }

    /**
     * Retourne le nom normalisé à donner au fichier physique associé à un Fichier.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string
     */
    private function computeFileNameForFichier(Fichier $fichier): string
    {
        return $fichier->getNom();
    }

    /**
     * Selon le storage, retourne le chemin absolu d'une copie ou du fichier original physique associé à un Fichier.
     *
     * En cas de fichier introuvable, possibilité de générer un PDF de substitution : cf.
     * {@see setGenererFichierSubstitutionSiIntrouvable()}.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string Chemin du fichier sur le filesystem
     *
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException Fichier introuvable mais génération
     * d'un PDF de substitution non activée
     */
    public function getFileForFichier(Fichier $fichier): string
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid();

        try {
            $this->storageAdapter->saveToFilesystem($dirPath, $fileName, $tmpFilePath);
        } catch (StorageAdapterException $e) {
            // en cas de fichier introuvable dans le storage, génération éventuelle d'un fichier de substitution
            if ($this->genererFichierSubstitutionSiIntrouvable) {
                $fichier->setPath($e->getDirPath() . '/' . $e->getFileName());
                $substitutionFileContent = $this->createSubstitutionFileContentForFichier($fichier);
                if ($substitutionFileContent === null ) {
                    throw $e; // solution de facilité (todo: lancer une exception spécifique)
                }
                file_put_contents($tmpFilePath, $substitutionFileContent);
            } else {
                throw $e;
            }
        }

        return $tmpFilePath;
    }

    /**
     * Retourne le contenu brut du fichier physique associé à un Fichier.
     *
     * En cas de fichier introuvable, possibilité de générer un PDF de substitution : cf.
     * {@see setGenererFichierSubstitutionSiIntrouvable()}.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string Contenu du fichier physique
     *
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException Fichier introuvable mais génération
     * d'un PDF de substitution non activée
     */
    public function getFileContentForFichier(Fichier $fichier): string
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        try {
            return $this->storageAdapter->getFileContent($dirPath, $fileName);
        } catch (StorageAdapterException $e) {
            // en cas de fichier introuvable dans le storage, génération éventuelle d'un fichier de substitution
            if ($this->genererFichierSubstitutionSiIntrouvable) {
                $fichier->setPath($e->getDirPath() . '/' . $e->getFileName());
                $substitutionFileContent = $this->createSubstitutionFileContentForFichier($fichier);
                if ($substitutionFileContent === null ) {
                    throw $e; // solution de facilité (todo: lancer une exception spécifique)
                }
                return $substitutionFileContent;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Enregistre le fichier physique associé à un Fichier.
     *
     * @param string $filepath
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileForFichier(string $filepath, Fichier $fichier)
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        $this->storageAdapter->saveFileContent(file_get_contents($filepath), $dirPath, $fileName);
    }

    /**
     * Supprime le fichier physique associé à un Fichier.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function deleteFileForFichier(Fichier $fichier)
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        $this->storageAdapter->deleteFile($dirPath, $fileName);
    }



    /********************************** Logos de structures *************************************/

    /**
     * Retourne le nom normalisé du répertoire parent du fichier physique du logo d'une structure.
     *
     * @param StructureInterface $structure Entité Structure concernée
     * @return string
     */
    private function computeDirectoryNameForLogoStructure(StructureInterface $structure): string
    {
        $type = $structure->getTypeStructure();
        $dir = null;

        // sous-répertoire identifiant le type de structure
        if ($type->isEtablissement()) {
            $dir = self::DIR_ETAB;
        } elseif ($type->isEcoleDoctorale()) {
            $dir = self::DIR_ED;
        } elseif ($type->isUniteRecherche()) {
            $dir = self::DIR_UR;
        }
        if ($dir === null) {
            throw new RuntimeException("Structure spécifiée imprévue.");
        }

        return $dir;
    }

    /**
     * Retourne le chemin normalisé de l'emplacement du fichier physique du logo d'une structure.
     *
     * @param StructureInterface $structure Entité Structure concernée
     * @return string
     */
    private function computeDirectoryPathForLogoStructure(StructureInterface $structure): string
    {
        $dir = $this->computeDirectoryNameForLogoStructure($structure);

        return $this->storageAdapter->computeDirectoryPath('ressources', 'Logos', $dir);
    }

    /**
     * Retourne le nom normalisé à donner au fichier physique du *nouveau* logo d'une structure.
     *
     * *NB : Cette méthode ne doit pas être utilisée pour obtenir le nom du fichier logo existant d'une structure.
     * Pour cela, il faut utiliser {@see StructureInterface::getCheminLogo()}.*
     *
     * @param StructureInterface $structure Entité Structure concernée
     * @return string
     */
    public function computeFileNameForNewLogoStructure(StructureInterface $structure): string
    {
        // Utiliser le 'code' de la structure (plutôt que le 'source_code' et/ou le 'sigle' comme précédemment),
        // qui n'est pas sensé changer, garantit que le calcul du chemin du fichier logo continuera de fonctionner
        // meme si :
        //   - la structure fait l'objet d'une substitution ;
        //   - le sigle de la structure change (ex : 'ED 558 HMPL' => 'ED 558 NH').

        if (!$structure->getCode()) {
            throw new InvalidArgumentException(sprintf(
                "Impossible de calculer le nom du fichier pour le logo de la structure %s car son 'code' est vide",
                $structure->getId()
            ));
        }

        $name = $structure->getCode();

        $name = str_replace(["'", ':'], '_', $name);
        $name = str_replace(' ', '', $name);

        return $name . ".png";
    }

    /**
     * Retourne le chemin absolu d'une copie du fichier physique du logo existant d'une structure.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure Entité Structure concernée
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException Fichier introuvable dans le storage
     */
    public function getFileForLogoStructure(StructureInterface $structure): ?string
    {
        if (!$structure->getCheminLogo()) {
            return null;
        }

        $dirPath = $this->computeDirectoryPathForLogoStructure($structure);
        $fileName = $structure->getCheminLogo();

        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid();

        try {
            $this->storageAdapter->saveToFilesystem($dirPath, $fileName, $tmpFilePath);
        } catch (StorageAdapterException $e) {
            // en cas de fichier introuvable dans le storage, génération éventuelle d'un fichier de substitution
            if ($this->genererFichierSubstitutionSiIntrouvable) {
                $fichier = (new Fichier())
                    ->setTypeMime('image/png')
                    ->setPath($e->getDirPath() . '/' . $e->getFileName());
                $substitutionFileContent = $this->createSubstitutionFileContentForFichier($fichier);
                if ($substitutionFileContent === null ) {
                    throw $e; // solution de facilité (todo: lancer une exception spécifique)
                }
                file_put_contents($tmpFilePath, $substitutionFileContent);
            } else {
                throw $e;
            }
        }

        return $tmpFilePath;
    }

    private function createSubstitutionFileContentForFichier(Fichier $fichier): ?string
    {
        if ($fichier->isTypeMimePdf()) {
            $tmpFilePath = $this->generatePageFichierIntrouvable($fichier->getPath());
            return file_get_contents($tmpFilePath);
        } elseif ($fichier->isTypeMimeImage()) {
            return Util::createImageWithText("Anomalie: Fichier|absent sur le storage. " . $fichier->getPath(), 200, 200);
        } else {
            return null;
        }
    }

    /**
     * Enregistre le fichier physique du nouveau logo d'une structure.
     *
     * @param string $logoFilepath
     * @param \Structure\Entity\Db\StructureInterface $structure
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileForLogoStructure(string $logoFilepath, StructureInterface $structure)
    {
        $logoDir = $this->computeDirectoryPathForLogoStructure($structure);
        $logoFilename = $this->computeFileNameForNewLogoStructure($structure);

        $this->storageAdapter->saveFileContent(file_get_contents($logoFilepath), $logoDir, $logoFilename);
    }

    /**
     * Supprime le fichier physique du logo existant d'une structure.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function deleteFileForLogoStructure(StructureInterface $structure)
    {
        $dirPath = $this->computeDirectoryPathForLogoStructure($structure);
        $fileName = $structure->getCheminLogo();

        $this->storageAdapter->deleteFile($dirPath, $fileName);
    }


    /***************************************************************************/


    /**
     * Migration des fichiers d'un storage à un autre.
     *
     * BROUILLON !
     *
     * TODO Pb : comment migrer les logos de structures puisqu'ils n'existent pas en tant que Fichier ?
     *
     * @param Fichier[] $fichiers
     * @param \Fichier\Service\Storage\Adapter\StorageAdapterInterface $fromStorage
     * @param \Fichier\Service\Storage\Adapter\StorageAdapterInterface $toStorage
     * @return \Generator
     */
    public function migrerFichiers(iterable $fichiers, StorageAdapterInterface $fromStorage, StorageAdapterInterface $toStorage): Generator
    {
        foreach ($fichiers as $fichier) {
            $dirName = $this->computeDirectoryNameForFichier($fichier);
            $filename = $this->computeFileNameForFichier($fichier);
            $fromDirPath = $fromStorage->computeDirectoryPath($dirName);
            $toDirPath = $toStorage->computeDirectoryPath($dirName);
            $exception = null;
            try {
                $content = $fromStorage->getFileContent($fromDirPath, $filename);
                $toStorage->saveFileContent($content, $toDirPath, $filename);
                unset($content);
            } catch (StorageAdapterException $e) {
                $exception = $e;
            }
            yield compact('fichier', 'fromDirPath', 'toDirPath', 'filename', 'exception');
            unset($fichier);
        }
    }
}