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
     *
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getFileContent(string $dirPath, string $fileName): string;

    /**
     * @param string $fromDirPath
     * @param string $fromFileName
     * @param string $toFilesystemPath
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveToFilesystem(string $fromDirPath, string $fromFileName, string $toFilesystemPath);

    /**
     * @param string $fileContent
     * @param string $toDirPath
     * @param string $toFileName
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function saveFileContent(string $fileContent, string $toDirPath, string $toFileName);
}