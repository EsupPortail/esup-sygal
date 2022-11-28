<?php

namespace StepStar\Service\Xsl;

use StepStar\Exception\TefServiceException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

class XslService
{
    /**
     * @var string
     */
    protected string $outputDir;

    /**
     * @var string
     */
    protected string $xslTemplatePath;

    /**
     * @var string[]
     */
    protected array $xslTemplateParams = [];

    /**
     * @param string $outputDir
     */
    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
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
     * TefService constructor.
     */
    public function __construct()
    {
        $this->outputDir = sys_get_temp_dir();
    }

    /**
     * Génération du fichier XSL à partir du template Twig.
     *
     * @param string|null $resultDocumentHref
     * @return string
     * @throws \StepStar\Exception\TefServiceException
     */
    public function generateXslFile(?string $resultDocumentHref = null): string
    {
        if ($this->outputDir === null) {
            throw new TefServiceException("Aucun répertoire destination n'a été spécifié");
        }

        // substitution possible
        if ($resultDocumentHref !== null) {
            $this->xslTemplateParams['resultDocumentHref'] = $resultDocumentHref;
        }

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
            $xslFileContent = $twig->render($templateFileName, $this->xslTemplateParams);
        } catch (Error $e) {
            throw new TefServiceException("Erreur Twig rencontrée", null, $e);
        }

        $xslFilePath = $this->outputDir . '/' . uniqid('sygal_xsl_') . '.xsl';
        file_put_contents($xslFilePath, $xslFileContent);

        return $xslFilePath;
    }
}