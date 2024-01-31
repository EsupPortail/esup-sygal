<?php

namespace Admission\Service\Exporter\ConventionFormationDoctorale;

trait ConventionFormationDoctoraleExporterAwareTrait {

    private ConventionFormationDoctoraleExporter $conventionFormationDoctoraleExporter;

    public function getConventionFormationDoctoraleExporter(): ConventionFormationDoctoraleExporter
    {
        return $this->conventionFormationDoctoraleExporter;
    }

    public function setConventionFormationDoctoraleExporter(ConventionFormationDoctoraleExporter $conventionFormationDoctoraleExporter): void
    {
        $this->conventionFormationDoctoraleExporter = $conventionFormationDoctoraleExporter;
    }

}