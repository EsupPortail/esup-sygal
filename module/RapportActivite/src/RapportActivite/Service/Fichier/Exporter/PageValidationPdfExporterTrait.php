<?php

namespace RapportActivite\Service\Fichier\Exporter;

trait PageValidationPdfExporterTrait
{
    protected PageValidationPdfExporter $pageValidationPdfExporter;

    /**
     * @param \RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter $pageValidationPdfExporter
     */
    public function setPageValidationPdfExporter(PageValidationPdfExporter $pageValidationPdfExporter): void
    {
        $this->pageValidationPdfExporter = $pageValidationPdfExporter;
    }
}