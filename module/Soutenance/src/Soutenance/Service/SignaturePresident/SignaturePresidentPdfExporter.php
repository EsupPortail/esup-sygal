<?php

namespace Soutenance\Service\SignaturePresident;

use Soutenance\Entity\PropositionThese;
use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class SignaturePresidentPdfExporter extends PdfExporter
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
        $this->setFooterScript('empty.phtml');
        if(isset($this->vars["proposition"]) && $this->vars["proposition"] instanceof PropositionThese){
            $this->addBodyScript('signature-president-these.phtml', false, $this->vars);
        }else{
            $this->addBodyScript('signature-president-hdr.phtml', false, $this->vars);
        }
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}