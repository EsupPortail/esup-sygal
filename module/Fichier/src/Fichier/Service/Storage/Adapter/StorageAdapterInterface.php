<?php

namespace Fichier\Service\Storage\Adapter;

interface StorageAdapterInterface
{
    /**
     * @param string ...$pathParts
     * @return string
     */
    public function computeDirectoryPath(string ...$pathParts): string;

    /**
     * @param string $dirPath
     * @param string $fileName
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function deleteFile(string $dirPath, string $fileName);

    /**
     * @param string $dirPath
     * @param string $fileName
     * @return string
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileContent(string $dirPath, string $fileName): string;

    /**
     * Enregistre *si besoin* le fichier sur le disque.
     * Sauf si le storage est de type Filesystem (disque), auquel cas le chemin retourné est l'emplacement original
     * du fichier.
     *
     * @param string $fromDirPath
     * @param string $fromFileName
     * @param string $toFilesystemPath Chemin où enregistrer le fichier sur le disque
     * @return string Chemin où a été enregistré le fichier, ou emplacement original dans le cas d'un storage Filesystem.
     *
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveToFilesystem(string $fromDirPath, string $fromFileName, string $toFilesystemPath): string;

    /**
     * @param string $fileContent
     * @param string $toDirPath
     * @param string $toFileName
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileContent(string $fileContent, string $toDirPath, string $toFileName);
}