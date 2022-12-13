<?php

namespace Soutenance\Service\Exporter\SermentExporter;

use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class SermentPdfExporter extends PdfExporter
{
    private array $vars = [];

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

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null) : string
    {
        $this->addBodyScript('serment.phtml', false, $this->vars);
        $this->setHeaderScript('header.phtml', null, $this->vars);
        $this->setFooterScript('footer.phtml');
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}