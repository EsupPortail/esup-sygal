<?php

namespace Soutenance\Service\Exporter\RapportSoutenance;

use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class RapportSoutenancePdfExporter extends PdfExporter
{
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
//        $this->addBodyHtml('<style>' . file_get_contents(APPLICATION_DIR . '/public/css/page-unicaen.css') . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('footer.phtml', null, $this->vars);
        $this->addBodyScript('rapport-soutenance.phtml', false, $this->vars);
        $this->addBodyScript('rapport-soutenance-2.phtml', true, $this->vars);
        $this->addBodyScript('rapport-soutenance-3.phtml', true, $this->vars);
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}