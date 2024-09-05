<?php

namespace These\Service\Exporter\CoEncadrements;

trait CoEncadrementsExporterAwareTrait {

    private CoEncadrementsExporter $coEncadrementsExporter;

    public function getCoEncadrementsExporter(): CoEncadrementsExporter
    {
        return $this->coEncadrementsExporter;
    }

    public function setCoEncadrementsExporter(CoEncadrementsExporter $coEncadrementsExporter): void
    {
        $this->coEncadrementsExporter = $coEncadrementsExporter;
    }

}