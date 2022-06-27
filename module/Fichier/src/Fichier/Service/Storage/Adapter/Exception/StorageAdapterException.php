<?php

namespace Fichier\Service\Storage\Adapter\Exception;

use Exception;

class StorageAdapterException extends Exception
{
    protected ?string $dirPath = null;
    protected ?string $fileName = null;

    /**
     * @return string|null
     */
    public function getDirPath(): ?string
    {
        return $this->dirPath;
    }

    /**
     * @param string|null $dirPath
     * @return self
     */
    public function setDirPath(?string $dirPath): self
    {
        $this->dirPath = $dirPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return self
     */
    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }
}