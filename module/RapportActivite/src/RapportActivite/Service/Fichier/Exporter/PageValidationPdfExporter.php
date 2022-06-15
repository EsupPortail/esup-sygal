<?php

namespace RapportActivite\Service\Fichier\Exporter;

use Application\Exporter\Pdf\ApplicationPdfExporter;

class PageValidationPdfExporter extends ApplicationPdfExporter
{
    protected function prepare()
    {
        parent::prepare();

        $this->setHeaderScriptToNone();
        $this->setFooterScriptToNone();
    }
}