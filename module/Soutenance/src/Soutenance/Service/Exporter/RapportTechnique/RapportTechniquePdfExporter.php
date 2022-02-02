<?php

namespace Soutenance\Service\Exporter\RapportTechnique;

use UnicaenApp\Exporter\Pdf as PdfExporter;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

class RapportTechniquePdfExporter extends PdfExporter
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
        $this->setFooterScript('footer.phtml');
        $this->addBodyScript('rapport-technique.phtml', false, $this->vars);
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}