<?php

namespace Soutenance\Service\Exporter\Convocation;

use Formation\Service\Exporter\Convocation\ConvocationExporter;

trait ConvocationExporterAwareTrait {

    private ConvocationExporter $convocationExporter;

    public function getConvocationExporter(): ConvocationExporter
    {
        return $this->convocationExporter;
    }

    public function setConvocationExporter(ConvocationExporter $convocationExporter): void
    {
        $this->convocationExporter = $convocationExporter;
    }

}