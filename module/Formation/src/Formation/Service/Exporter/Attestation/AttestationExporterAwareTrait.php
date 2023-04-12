<?php

namespace Formation\Service\Exporter\Attestation;

trait AttestationExporterAwareTrait {

    private AttestationExporter $attestationExporter;

    public function getAttestationExporter(): AttestationExporter
    {
        return $this->attestationExporter;
    }

    public function setAttestationExporter(AttestationExporter $attestationExporter): void
    {
        $this->attestationExporter = $attestationExporter;
    }

}