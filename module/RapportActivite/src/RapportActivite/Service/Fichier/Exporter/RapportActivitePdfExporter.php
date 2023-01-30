<?php

namespace RapportActivite\Service\Fichier\Exporter;

use Application\Exporter\Pdf\ApplicationPdfExporter;

class RapportActivitePdfExporter extends ApplicationPdfExporter
{
    protected function prepare()
    {
        parent::prepare();

        $this->setHeaderScriptToNone();
    }
}