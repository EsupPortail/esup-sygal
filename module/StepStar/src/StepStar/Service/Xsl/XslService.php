<?php

namespace StepStar\Service\Xsl;

use Saxon\SaxonProcessor;
use Saxon\Xslt30Processor;
use StepStar\Exception\XslServiceException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Webmozart\Assert\Assert;

class XslService
{
    /**
     * @var SaxonProcessor
     */
    protected $saxonProcessor;

    /**
     * @var Xslt30Processor
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        Assert::keyExists($this->config, 'xsl_twig_template_path');
        Assert::keyExists($this->config, 'xsl_twig_template_params');
        Assert::keyExists($this->config, 'xsl_file_path');

        return $this;
    }

    /**
     * Génération des fichiers XML TEF dans le répertoire de sortie spécifié.
     *
     * @param string $thesesXmlFilepath Fichier XML source
     * @param string $outputDir Répertoire où générer les fichiers
     * @throws XslServiceException
     */
    public function transformToFiles(string $thesesXmlFilepath, string $outputDir)
    {
        if ($outputDir === null) {
            throw new XslServiceException("Aucun répertoire destination n'a été spécifié");
        }
        if (file_exists($outputDir)) {
            throw new XslServiceException("Le répertoire destination spécifié existe déjà : " . $outputDir);
        }
        mkdir($outputDir, 0777, true);

        $xslFilePath = $this->config['xsl_file_path'];

        // Si le fichier XSL spécifié n'existe pas, on doit le générer à partir du template Twig
        if (!file_exists($xslFilePath)) {
            $xslFileContent = $this->generateXslFileContent();
            //$xslFilePath = $outputDir . '/' . uniqid('stepstar_') . '.xsl';
            file_put_contents($xslFilePath, $xslFileContent);
        }

        // Il faut spécifier un fichier mais c'est son répertoire qui recevra les fichiers générés
        $outputFilepath = $outputDir . '/dummy.xml';

        $this->saxonProcessor = new SaxonProcessor();
        $this->transformer = $this->saxonProcessor->newXslt30Processor();
        $this->transformer->setInitialMatchSelectionAsFile($thesesXmlFilepath);
        $this->transformer->applyTemplatesReturningFile($xslFilePath, $outputFilepath);
        $this->transformer->clearParameters();
        $this->transformer->clearProperties();
        unlink($outputFilepath);
    }

    /**
     * Génération du contenu du fichier XSL à partir du template Twig.
     *
     * @return string
     * @throws XslServiceException
     */
    private function generateXslFileContent(): string
    {
        $xslTwigTemplatePath = $this->config['xsl_twig_template_path'];
        $xslTwigTemplateParams = $this->config['xsl_twig_template_params'];

        if (!file_exists($xslTwigTemplatePath)) {
            throw new XslServiceException("Template $xslTwigTemplatePath introuvable.");
        }

        $templatesDir = dirname($xslTwigTemplatePath);
        $loader = new FilesystemLoader($templatesDir);
        $twig = new Environment($loader, [
//            'cache' => '/path/to/compilation_cache',
        ]);

        $templateFileName = basename($xslTwigTemplatePath);
        try {
            return $twig->render($templateFileName, $xslTwigTemplateParams);
        } catch (Error $e) {
            throw new XslServiceException("Erreur Twig rencontrée", null, $e);
        }
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->transformer->getExceptionCount() > 0;
    }

    /**
     * @return array [code => message]
     */
    public function getErrors(): array
    {
        $errors = [];

        $errCount = $this->transformer->getExceptionCount();
        if ($errCount > 0) {
            for ($i = 0; $i < $errCount; $i++) {
                $errCode = $this->transformer->getErrorCode($i);
                $errMessage = $this->transformer->getErrorMessage($i);
                $errors[$errCode] = $errMessage;
            }
            $this->transformer->exceptionClear();
        }

        return $errors;
    }
}