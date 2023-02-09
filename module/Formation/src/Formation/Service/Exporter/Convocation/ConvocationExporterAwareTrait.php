<?php

namespace Formation\Service\Exporter\Convocation;

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