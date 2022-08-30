<?php

namespace Fichier\Exporter;

use Application\Exporter\Pdf\ApplicationPdfExporter;

class PageFichierIntrouvablePdfExporter extends ApplicationPdfExporter
{
    protected function prepare()
    {
        parent::prepare();

        $this->setHeaderScriptToNone();
        $this->setFooterScriptToNone();
    }
}