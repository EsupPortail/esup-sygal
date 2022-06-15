<?php

namespace Application\Exporter\Pdf;

use InvalidArgumentException;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenApp\Exporter\Pdf as PdfExporter;

class ApplicationPdfExporter extends PdfExporter
{
    /**
     * @var string
     */
    protected string $templateFilePath;

    /**
     * @var string|null
     */
    protected ?string $cssFilePath = null;

    /**
     * @var array
     */
    protected array $vars = [];

    /**
     * Spécifie le chemin absolu du template PHTML à transformer en PDF.
     *
     * @param string $templateFilePath
     * @return $this
     */
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

    /**
     * Spécifie le chemin absolu de la feuille de styles, le cas écéhant.
     *
     * @param string|null $cssFilePath
     * @return $this
     */
    public function setCssFilePath(?string $cssFilePath): self
    {
        if ($cssFilePath && !is_readable($cssFilePath)) {
            throw new InvalidArgumentException(
                "Le fichier css spécifié pour la page de couverture n'existe pas ou est inaccessible : " . $cssFilePath
            );
        }

        $this->cssFilePath = $cssFilePath;

        return $this;
    }

    /**
     * Spécifie le tableau des variables à passer au moteur de rendu.
     *
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars): self
    {
        $this->vars = $vars;
        $this->vars['exporter'] = $this;

        return $this;
    }

    protected function prepare()
    {
        $templateDirPath = dirname($this->templateFilePath);
        $templateFileName = basename($this->templateFilePath);

        $resolver = $this->renderer->resolver();
        $resolver->attach(new TemplatePathStack(['script_paths' => [
            $templateDirPath,
        ]]));

        if ($this->cssFilePath) {
            $this->addBodyHtml('<style>' . file_get_contents($this->cssFilePath) . '</style>');
        }
        $this->addBodyScript($templateFileName, false, $this->vars);
    }

    /**
     * @inheritDoc
     */
    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $this->prepare();

        return PdfExporter::export(basename($filename), $destination, $memoryLimit);
    }
}