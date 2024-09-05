<?php

namespace These\Service\Exporter\CoEncadrements;

use Application\Service\Role\RoleServiceAwareTrait;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use These\Provider\Template\PdfTemplates;
use UnicaenPdf\Exporter\PdfExporter as PdfExporter;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

class CoEncadrementsExporter extends PdfExporter
{
    use RenduServiceAwareTrait;
    use RoleServiceAwareTrait;
    private $vars;

    public function setVars(array $vars)
    {
        $this->vars = $vars;
        $this->vars['exporter'] = $this;

        return $this;
    }

    public function __construct(PhpRenderer $renderer = null, $format = 'A4', $orientationPaysage = false, $defaultFontSize = 10)
    {
        parent::__construct($renderer, $format, $orientationPaysage, $defaultFontSize);
        $resolver = $renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [__DIR__]]));
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $logos = $this->vars['logos'];
        $listing = $this->vars["listing"];
        $vars = [
            'acteur' => $this->vars['acteur'],
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(PdfTemplates::COENCADREMENTS_JUSTIFICATIF, $vars);
        $corps = str_replace("###LISTING_THESE###", $listing, $rendu->getCorps());
        $this->getMpdf()->SetMargins(0, 0, 60);
        $this->setHeaderScript("these/pdf/coencadrant-header.phtml", null, $logos);
        $this->setFooterScript('these/pdf/coencadrant-footer.phtml');
        $this->addBodyHtml($corps);

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}