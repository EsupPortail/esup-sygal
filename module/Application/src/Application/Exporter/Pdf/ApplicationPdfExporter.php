<?php

namespace Application\Exporter\Pdf;

use InvalidArgumentException;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplatePathStack;
use UnicaenApp\Exporter\Pdf;
use UnicaenApp\Exporter\Pdf as PdfExporter;

/**
 * !!!!!! Attention !!!!!!
 * En l'état actuel, la classe d'export PDF {@see \UnicaenApp\Exporter\Pdf} ne sait pas faire correctement de
 * génération PDF par lot donc mieux vaut cloner l'instance pour chaque génération/export.
 *
 */
class ApplicationPdfExporter extends PdfExporter
{
    /**
     * @var string
     */
    protected string $templateFilePath;

    /**
     * @var string[]
     */
    protected array $cssFilePaths = [];

    /**
     * @var array
     */
    protected array $vars = [];

    /**
     * @inheritDoc
     */
    public function __construct(PhpRenderer $renderer = null, $format = 'A4', $orientationPaysage = false, $defaultFontSize = 10)
    {
        // création d'un renderer tout neuf nécessaire pour éviter la collision de noms de templates dans le resolver
        if ($renderer === null) {
            $renderer = new PhpRenderer();
            $renderer->setResolver(new AggregateResolver());
        }

        parent::__construct($renderer, $format, $orientationPaysage, $defaultFontSize);
    }

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
                "Le template spécifié n'existe pas ou est inaccessible : " . $templateFilePath
            );
        }

        $this->templateFilePath = $templateFilePath;

        return $this;
    }

    /**
     * Spécifie les chemins absolus des feuilles de styles dont le contenu sera ajouté au HTML.
     *
     * @param string[] $cssFilePaths
     * @return $this
     */
    public function setCssFilePaths(array $cssFilePaths): self
    {
        foreach ($cssFilePaths as $path) {
            if (!is_readable($path)) {
                throw new InvalidArgumentException(
                    "Le fichier CSS spécifié n'existe pas ou est inaccessible : " . $path
                );
            }
            $this->cssFilePaths[] = $path;
        }

        return $this;
    }

    /**
     * Spécifie le chemin absolu de la feuille de styles, le cas écéhant.
     *
     * @param string|null $cssFilePath
     * @return $this
     *
     * @deprecated Utiliser {@see setCssFilePaths()}
     */
    public function setCssFilePath(?string $cssFilePath): self
    {
        if ($cssFilePath && !is_readable($cssFilePath)) {
            throw new InvalidArgumentException(
                "Le fichier css spécifié pour la page de couverture n'existe pas ou est inaccessible : " . $cssFilePath
            );
        }

        $this->cssFilePaths = (array) $cssFilePath;

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

        foreach ($this->cssFilePaths as $path) {
            $this->addBodyHtml('<style>' . file_get_contents($path) . '</style>', false);
        }
        $this->addBodyScript($templateFileName, false, $this->vars);
    }

    /**
     * @inheritDoc
     */
    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        $this->prepare();

        PdfExporter::export(basename($filename), $destination, $memoryLimit);
    }
}