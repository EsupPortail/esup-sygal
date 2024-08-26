<?php

namespace Depot\Service\ConventionDeMiseEnLigne\Exporter;

trait ConventionDeMiseEnLigneExporterAwareTrait
{
    protected ConventionDeMiseEnLigneExporter $conventionDeMiseEnLigneExporter;

    public function setConventionDeMiseEnLigneExporter(ConventionDeMiseEnLigneExporter $conventionDeMiseEnLigneExporter): void
    {
        $this->conventionDeMiseEnLigneExporter = $conventionDeMiseEnLigneExporter;
    }
}