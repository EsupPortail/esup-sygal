<?php

namespace Fichier\Entity;

use Fichier\Entity\Db\Fichier;

/**
 * Adaptateur de {@see \Fichier\Entity\Db\Fichier} ajoutant des caractéristiques utiles
 * pour une création d'archive.
 */
class FichierArchivable
{
    /**
     * Fichier original.
     */
    private Fichier $fichier;

    /**
     * Chemin de ce fichier archivable sur le filesystem, remplaçant celui du fichier original.
     */
    private ?string $filePath = null;

    /**
     * Chemin interne du fichier dans l'archive.
     */
    private string $filePathInArchive;


    public function __construct(Fichier $fichier)
    {
        $this->fichier = $fichier;
    }

    /**
     * @return \Fichier\Entity\Db\Fichier
     */
    public function getFichier(): Fichier
    {
        return $this->fichier;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath ?: $this->fichier->getPath();
    }

    /**
     * @param string|null $filePath
     * @return self
     */
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePathInArchive(): string
    {
        return $this->filePathInArchive;
    }

    /**
     * @param string $filePathInArchive
     * @return self
     */
    public function setFilePathInArchive(string $filePathInArchive): self
    {
        $this->filePathInArchive = $filePathInArchive;
        return $this;
    }

}