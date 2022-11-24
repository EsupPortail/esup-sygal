<?php

namespace Depot\Service\PageDeCouverture;

trait PageDeCouverturePdfExporterAwareTrait
{
    /**
     * @var \Depot\Service\PageDeCouverture\PageDeCouverturePdfExporter
     */
    protected $pageDeCouverturePdfExporter;

    public function setPageDeCouverturePdfExporter(PageDeCouverturePdfExporter $exporter)
    {
        $this->pageDeCouverturePdfExporter = $exporter;
    }
}