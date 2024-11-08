<?php

namespace Soutenance\Service\Exporter\Convocation;

use Application\Entity\Db\Role;
use These\Entity\Db\These;
use Soutenance\Entity\Membre;
use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class ConvocationPdfExporter extends PdfExporter
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
//        $this->allow_charset_conversion = true;
//        $this->charset_in='UTF-8';
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');

        $this->addBodyScript('convocation_doctorant.phtml', false, $this->vars);
        $this->addBodyScript('empty.phtml');

        /** @var These $these */
        $these = $this->vars["these"];
        /** @var \These\Entity\Db\Acteur[] $jury */
        $jury = $these->getActeursByRoleCode(Role::CODE_MEMBRE_JURY)->toArray();

        foreach ($jury as $acteur) {
            $this->vars["acteur"] = $acteur;
            $this->addBodyScript('convocation_membre.phtml', true, $this->vars);
            $this->addBodyScript('empty.phtml');
        }

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }

    public function exportDoctorant($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        /** @var These $these */
        $these = $this->vars["these"];

//        $this->addBodyHtml('<style>' . file_get_contents(APPLICATION_DIR . '/public/css/page-unicaen.css') . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');

        $this->addBodyScript('convocation_doctorant.phtml', false, $this->vars);

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }

    public function exportMembre(Membre $membre, $filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        /** @var These $these */
        $these = $this->vars["these"];

//        $this->addBodyHtml('<style>' . file_get_contents(APPLICATION_DIR . '/public/css/page-unicaen.css') . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');

        $this->vars["membre"] = $membre;
        $this->addBodyScript('convocation_membre.phtml', false, $this->vars);

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}