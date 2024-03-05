<?php

namespace Admission\Service\Exporter\Recapitulatif;

trait RecapitulatifExporterAwareTrait {

    private RecapitulatifExporter $recapitulatifExporter;

    public function getRecapitulatifExporter(): RecapitulatifExporter
    {
        return $this->recapitulatifExporter;
    }

    public function setRecapitulatifExporter(RecapitulatifExporter $recapitulatifExporter): void
    {
        $this->recapitulatifExporter = $recapitulatifExporter;
    }

}