<?php

namespace Soutenance\Service\Exporter\Convocation;

use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Soutenance\Entity\Membre;
use UnicaenApp\Exporter\Pdf as PdfExporter;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

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
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $this->addBodyHtml('<style>' . file_get_contents(APPLICATION_DIR . '/public/css/page-unicaen.css') . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');

        $this->addBodyScript('convocation_doctorant.phtml', false, $this->vars);
        $this->addBodyScript('empty.phtml');

        /** @var These $these */
        $these = $this->vars["these"];
        /** @var Membre[] $jury */
        $jury = $these->getActeursByRoleCode(Role::CODE_MEMBRE_JURY)->toArray();

        foreach ($jury as $membre) {
            $this->vars["membre"] = $membre;
            $this->addBodyScript('convocation_membre.phtml', true, $this->vars);
            $this->addBodyScript('empty.phtml');
        }


        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}