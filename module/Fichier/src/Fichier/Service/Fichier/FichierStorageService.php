<?php

namespace Fichier\Service\Fichier;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureInterface;
use Structure\Entity\Db\UniteRecherche;
use Fichier\Entity\Db\Fichier;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\Storage\Adapter\StorageAdapterInterface;
use Generator;
use RuntimeException;

class FichierStorageService
{
    const DIR_ETAB = 'Etab';
    const DIR_ED = 'ED';
    const DIR_UR = 'UR';

    private StorageAdapterInterface $storageAdapter;

    public function setStorageAdapter(StorageAdapterInterface $storageAdapter): void
    {
        $this->storageAdapter = $storageAdapter;
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
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileForFichier(Fichier $fichier): string
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        $this->storageAdapter->saveToFilesystem(
            $dirPath,
            $fileName,
            $tmpFilePath = tempnam(sys_get_temp_dir(), '')
        );

        return $tmpFilePath;
    }

    /**
     * Retourne le contenu brut du fichier physique associé à un Fichier.
     *
     * @param \Fichier\Entity\Db\Fichier $fichier Entité Fichier concernée
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileContentForFichier(Fichier $fichier): string
    {
        $dirName = $this->computeDirectoryNameForFichier($fichier);
        $fileName = $this->computeFileNameForFichier($fichier);
        $dirPath = $this->storageAdapter->computeDirectoryPath($dirName);

        return $this->storageAdapter->getFileContent($dirPath, $fileName);
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
        $dir = null;

        // sous-répertoire identifiant le type de structure
        if ($structure instanceof EcoleDoctorale) {
            $dir = self::DIR_ED;
        } elseif ($structure instanceof UniteRecherche) {
            $dir = self::DIR_UR;
        } elseif ($structure instanceof Etablissement) {
            $dir = self::DIR_ETAB;
        } elseif ($structure instanceof Structure) {
            if ($structure->getTypeStructure()->isEtablissement()) {
                $dir = self::DIR_ETAB;
            } elseif ($structure->getTypeStructure()->isEcoleDoctorale()) {
                $dir = self::DIR_ED;
            } elseif ($structure->getTypeStructure()->isUniteRecherche()) {
                $dir = self::DIR_UR;
            }
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
     * Retourne le nom normalisé à donner au fichier physique du logo d'une structure.
     *
     * @param StructureInterface $structure Entité Structure concernée
     * @return string
     */
    public function computeFileNameForLogoStructure(StructureInterface $structure): string
    {
        if ($structure instanceof Etablissement) {
            $name = $structure->getStructure()->getCode() ?: $structure->generateUniqCode();
        } else {
            $name = $structure->getSourceCode() . "-" . $structure->getSigle();
        }

        return $name . ".png";
    }

    /**
     * Retourne le chemin absolu d'une copie ou de l'original (selon le storage) du fichier logo physique d'une structure.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure Entité Structure concernée
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileForLogoStructure(StructureInterface $structure): string
    {
        $dirPath = $this->computeDirectoryPathForLogoStructure($structure);
        $fileName = $this->computeFileNameForLogoStructure($structure);

        $this->storageAdapter->saveToFilesystem($dirPath, $fileName, $tmpFilePath = tempnam(sys_get_temp_dir(), ''));

        return $tmpFilePath;
    }

    /**
     * Retourne le contenu brut du fichier logo physique d'une structure.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure Entité Structure concernée
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileContentLogoStructure(StructureInterface $structure): string
    {
        $dirPath = $this->computeDirectoryPathForLogoStructure($structure);
        $fileName = $this->computeFileNameForLogoStructure($structure);

        return $this->storageAdapter->getFileContent($dirPath, $fileName);
    }

    /**
     * Enregistre le fichier logo physique d'une structure.
     *
     * @param string $logoFilepath
     * @param \Structure\Entity\Db\StructureInterface $structure
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileForLogoStructure(string $logoFilepath, StructureInterface $structure)
    {
        $logoDir = $this->computeDirectoryPathForLogoStructure($structure);
        $logoFilename = $this->computeFileNameForLogoStructure($structure);

        $this->storageAdapter->saveFileContent(file_get_contents($logoFilepath), $logoDir, $logoFilename);
    }

    /**
     * Supprime le fichier physique logo d'une structure.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function deleteFileForLogoStructure(StructureInterface $structure)
    {
        $dirPath = $this->computeDirectoryPathForLogoStructure($structure);
        $fileName = $this->computeFileNameForLogoStructure($structure);

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