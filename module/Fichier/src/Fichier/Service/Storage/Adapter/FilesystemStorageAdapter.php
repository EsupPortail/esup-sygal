<?php

namespace Fichier\Service\Storage\Adapter;

use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use UnicaenApp\Exception\RuntimeException;

class FilesystemStorageAdapter extends AbstractStorageAdapter
{
    protected string $pathSeparator = DIRECTORY_SEPARATOR;

    /**
     * @param array $config
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        if (! is_readable($this->rootPath)) {
            throw new RuntimeException(
                "Le chemin du répertoire de destination des fichiers doit exister et être accessible : " . $this->rootPath);
        }
    }

    /**
     * @inheritDoc
     */
    public function computeDirectoryPath(string ...$pathParts): string
    {
        $path = parent::computeDirectoryPath(...$pathParts);

        return strtr($path, "' ", '__');
    }

    /**
     * Création *si besoin* du dossier spécifié par son chemin absolu.
     *
     * @param string $absolutePath
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function createDirectory(string $absolutePath)
    {
        $ok = $this->createWritableFolder($absolutePath, 0770);
        if (!$ok) {
            throw new StorageAdapterException("Le répertoire suivant n'a pas pu être créé : " . $absolutePath);
        }
    }

    /**
     * Create correctly writable folder.
     *
     * Check if folder exist and writable.
     * If not exist try to create it one writable.
     *
     * @param string $folder
     * @param int $mode
     * @return bool true: folder has been created or exist and is writable.
     *              false: folder does not exist and cannot be created.
     *
     * @codeCoverageIgnore
     */
    public function createWritableFolder(string $folder, int $mode = 0700): bool
    {
        if($folder !== '.' && $folder !== '/' ) {
            $this->createWritableFolder(dirname($folder));
        }
        if (file_exists($folder)) {
            return is_writable($folder);
        }

        return mkdir($folder, $mode, true) && is_writable($folder);
    }

    public function deleteFile(string $dirPath, string $fileName)
    {
        $filePath = implode($this->pathSeparator, [$dirPath, $fileName]);

        if (! file_exists($filePath)) {
            throw (new StorageAdapterException("Le fichier suivant est introuvable : " . $filePath))
                ->setDirPath($dirPath)
                ->setFileName($fileName);
        }
        if (! unlink($filePath)) {
            throw (new StorageAdapterException("Impossible de supprimer le fichier suivant : " . $filePath))
                ->setDirPath($dirPath)
                ->setFileName($fileName);
        }
    }

    /**
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileContent(string $dirPath, string $fileName): string
    {
        $filePath = $this->assemblePath($dirPath, $fileName);

        if (!is_readable($filePath)) {
            throw new StorageAdapterException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        return file_get_contents($filePath);

    }

    /**
     * @param string $fromDirPath
     * @param string $fromFileName
     * @param string $toFilesystemPath
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveToFilesystem(string $fromDirPath, string $fromFileName, string $toFilesystemPath)
    {
        $filePath = implode($this->pathSeparator, [$fromDirPath, $fromFileName]);

        if (!is_readable($filePath)) {
            throw new StorageAdapterException(
                "Le fichier suivant n'existe pas ou n'est pas accessible sur le serveur : " . $filePath);
        }

        copy($filePath, $toFilesystemPath);
    }

    /**
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileContent(string $fileContent, string $toDirPath, string $toFileName)
    {
        $this->createDirectory($toDirPath);

        $filePath = $this->assemblePath($toDirPath, $toFileName);

        $res = file_put_contents($filePath, $fileContent);
        if ($res === false) {
            throw new StorageAdapterException("L'enregistrement du contenu dans '$filePath' a échoué");
        }
    }
}