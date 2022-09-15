<?php

namespace These\Service\PageDeCouverture;

trait PageDeCouverturePdfExporterAwareTrait
{
    /**
     * @var \These\Service\PageDeCouverture\PageDeCouverturePdfExporter
     */
    protected $pageDeCouverturePdfExporter;

    public function setPageDeCouverturePdfExporter(PageDeCouverturePdfExporter $exporter)
    {
        $this->pageDeCouverturePdfExporter = $exporter;
    }
}