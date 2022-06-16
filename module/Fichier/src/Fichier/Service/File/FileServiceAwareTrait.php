<?php

namespace Fichier\Service\File;

trait FileServiceAwareTrait
{
    /**
     * @var FileService
     */
    protected $fileService;

    /**
     * @param FileService $fileService
     */
    public function setFileService(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
}