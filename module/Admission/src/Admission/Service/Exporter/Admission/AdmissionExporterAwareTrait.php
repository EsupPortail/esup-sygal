<?php

namespace Admission\Service\Exporter\Admission;

trait AdmissionExporterAwareTrait {

    private AdmissionExporter $admissionExporter;

    public function getAdmissionExporter(): AdmissionExporter
    {
        return $this->admissionExporter;
    }

    public function setAdmissionExporter(AdmissionExporter $admissionExporter): void
    {
        $this->admissionExporter = $admissionExporter;
    }
}