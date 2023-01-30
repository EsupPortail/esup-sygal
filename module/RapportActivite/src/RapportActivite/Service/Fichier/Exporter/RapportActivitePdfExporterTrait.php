<?php

namespace RapportActivite\Service\Fichier\Exporter;

trait RapportActivitePdfExporterTrait
{
    protected RapportActivitePdfExporter $rapportActivitePdfExporter;

    /**
     * @param \RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporter $rapportActivitePdfExporter
     */
    public function setRapportActivitePdfExporter(RapportActivitePdfExporter $rapportActivitePdfExporter): void
    {
        $this->rapportActivitePdfExporter = $rapportActivitePdfExporter;
    }
}