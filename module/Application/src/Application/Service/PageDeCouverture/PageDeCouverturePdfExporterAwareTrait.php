<?php

namespace Application\Service\PageDeCouverture;

trait PageDeCouverturePdfExporterAwareTrait
{
    /**
     * @var \Application\Service\PageDeCouverture\PageDeCouverturePdfExporter
     */
    protected $pageDeCouverturePdfExporter;

    public function setPageDeCouverturePdfExporter(PageDeCouverturePdfExporter $exporter)
    {
        $this->pageDeCouverturePdfExporter = $exporter;
    }
}