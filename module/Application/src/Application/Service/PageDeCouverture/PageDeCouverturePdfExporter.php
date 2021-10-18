<?php

namespace Application\Service\PageDeCouverture;

use InvalidArgumentException;
use UnicaenApp\Exporter\Pdf as PdfExporter;
use Laminas\View\Resolver\TemplatePathStack;

class PageDeCouverturePdfExporter extends PdfExporter
{
    /**
     * @var string
     */
    private $templateFilePath = __DIR__ . '/pagedecouverture.phtml';

    /**
     * @var string
     */
    private $cssFilePath = __DIR__ . '/page-unicaen.css';

    /**
     * @var array
     */
    private $vars;

    public function setTemplateFilePath(string $templateFilePath): self
    {
        if (!is_readable($templateFilePath)) {
            throw new InvalidArgumentException(
                "Le template spécifié pour la page de couverture n'existe pas ou est inaccessible : " . $templateFilePath
            );
        }

        $this->templateFilePath = $templateFilePath;

        return $this;
    }

    public function setCssFilePath(string $cssFilePath): self
    {
        if (!is_readable($cssFilePath)) {
            throw new InvalidArgumentException(
                "Le fichier css spécifié pour la page de couverture n'existe pas ou est inaccessible : " . $cssFilePath
            );
        }

        $this->cssFilePath = $cssFilePath;

        return $this;
    }

    public function setVars(array $vars): self
    {
        $this->vars = $vars;
        $this->vars['exporter'] = $this;

        return $this;
    }

    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $templateDirPath = dirname($this->templateFilePath);
        $templateFileName = basename($this->templateFilePath);

        $resolver = $this->renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [
            __DIR__, // nécessaire pour le script 'empty.phml' (doit être en 1er, c'est important)
            $templateDirPath,
        ]]));

        $this->addBodyHtml('<style>' . file_get_contents($this->cssFilePath) . '</style>');
        $this->setHeaderScript('empty.phtml');
        $this->setFooterScript('empty.phtml');
        $this->addBodyScript($templateFileName, false, $this->vars);
        if (isset($this->vars['recto/verso']) AND $this->vars['recto/verso'] === true) {
            $this->addBodyScript('empty.phtml', true, $this->vars);
        }

        return PdfExporter::export($filename, $destination, $memoryLimit);
    }
}