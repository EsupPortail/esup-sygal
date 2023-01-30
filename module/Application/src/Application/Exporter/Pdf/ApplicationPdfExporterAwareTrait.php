<?php

namespace Application\Exporter\Pdf;

trait ApplicationPdfExporterAwareTrait
{
    protected ApplicationPdfExporter $appplicationPdfExporter;

    public function setAppplicationPdfExporter(ApplicationPdfExporter $appplicationPdfExporter): void
    {
        $this->appplicationPdfExporter = $appplicationPdfExporter;
    }
}