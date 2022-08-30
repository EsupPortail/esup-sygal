<?php

namespace Fichier\Exporter;

class PageFichierIntrouvablePdfExporterData
{
    private string $filePath;

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }
}