<?php

namespace Application\Service\PageDeCouverture;

use Application\Exporter\Pdf\ApplicationPdfExporter;

class PageDeCouverturePdfExporter extends ApplicationPdfExporter
{
    protected function prepare()
    {
        parent::prepare();

        $this->setHeaderScriptToNone();
        $this->setFooterScriptToNone();

        if (isset($this->vars['recto/verso']) AND $this->vars['recto/verso'] === true) {
            $this->addBodyHtml('', true, $this->vars);
        }
    }
}