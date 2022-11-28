<?php

namespace Depot\Service\PageDeCouverture;

use Application\Exporter\Pdf\ApplicationPdfExporter;

/**
 * @todo : déplacer dans le module These ?
 */
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