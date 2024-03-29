<?php

namespace Formation\Service\Exporter\Emargement;

use Formation\Entity\Db\Seance;
use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class EmargementExporter extends PdfExporter
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
        //$this->addBodyHtml('<style>' . file_get_contents('/css/app.css') . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');
        $this->addBodyScript('emargement.phtml', false, $this->vars);
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }

    /**
     * @param Seance[] $seances
     * @param null $filename
     * @param string $destination
     * @param null $memoryLimit
     * @return string
     */
    public function exportAll($seances, $filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $first = true;
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');
        foreach ($seances as $journee) {
            $this->vars["seance"] = $journee;
            $this->addBodyScript('emargement.phtml', !$first, $this->vars);
            $first = false;
        }
        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}