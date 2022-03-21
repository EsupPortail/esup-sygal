<?php

namespace StepStar\Service\Tef;

use StepStar\Exception\TefServiceException;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xslt\XsltServiceAwareTrait;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

class TefService
{
    use XmlServiceAwareTrait;
    use XsltServiceAwareTrait;

    /**
     * @var string
     */
    protected string $xslTemplatePath;

    /**
     * @var string[]
     */
    protected array $xslTemplateParams = [];

    /**
     * @var string
     */
    protected string $outputDir;

    /**
     * TefService constructor.
     */
    public function __construct()
    {
        $this->outputDir = sys_get_temp_dir();
    }

    /**
     * @param string $xslTemplatePath
     */
    public function setXslTemplatePath(string $xslTemplatePath): void
    {
        $this->xslTemplatePath = $xslTemplatePath;
    }

    /**
     * @param string[] $xslTemplateParams
     */
    public function setXslTemplateParams(array $xslTemplateParams): void
    {
        $this->xslTemplateParams = $xslTemplateParams;
    }

    /**
     * @param string $outputDir
     */
    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    /**
     * Transforme via XSLT le fichier XML spécifié contenant les thèses pour produire les fichiers TEF.
     *
     * @param string $xmlFilePath
     * @param string|null $resultDocumentHref
     * @throws \StepStar\Exception\TefServiceException
     */
    public function generateTefFilesFromXml(string $xmlFilePath, ?string $resultDocumentHref = null)
    {
        if ($this->outputDir === null) {
            throw new TefServiceException("Aucun répertoire destination n'a été spécifié");
        }
        if (file_exists($this->outputDir)) {
            throw new TefServiceException("Le répertoire destination spécifié existe déjà : " . $this->outputDir);
        }
        mkdir($this->outputDir, 0777, true);

        // substitution possible de paramètres
        if ($resultDocumentHref !== null) {
            $this->xslTemplateParams['resultDocumentHref'] = $resultDocumentHref;
        }

        // génération du .xsl
        $xslFileContent = $this->generateXslFileContent();
        $xslFilePath = $this->outputDir . '/' . uniqid('stepstar_') . '.xsl';
        file_put_contents($xslFilePath, $xslFileContent);

        // transformation XSLT
        $this->xsltService
            ->setOutputDir($this->outputDir)
            ->setXslFilePath($xslFilePath)
            ->transformToFiles($xmlFilePath);
    }

    /**
     * Génération du fichier XSL à partir du template Twig.
     *
     * @return string
     * @throws TefServiceException
     */
    private function generateXslFileContent(): string
    {
        $templateFile = $this->xslTemplatePath;
        if (!file_exists($templateFile)) {
            throw new TefServiceException("Template $templateFile introuvable.");
        }

        $templatesDir = dirname($templateFile);
        $loader = new FilesystemLoader($templatesDir);
        $twig = new Environment($loader, [
//            'cache' => '/path/to/compilation_cache',
        ]);

        $templateFileName = basename($templateFile);
        try {
            return $twig->render($templateFileName, $this->xslTemplateParams);
        } catch (Error $e) {
            throw new TefServiceException("Erreur Twig rencontrée", null, $e);
        }
    }
}