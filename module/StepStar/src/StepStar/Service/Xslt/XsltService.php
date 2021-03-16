<?php

namespace StepStar\Service\Xslt;

use Saxon\SaxonProcessor;
use Saxon\Xslt30Processor;

class XsltService
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
     * @var string
     */
    protected $outputDir;

    /**
     * @param string $outputDir
     * @return self
     */
    public function setOutputDir(string $outputDir): self
    {
        $this->outputDir = $outputDir;
        return $this;
    }

    /**
     * Génération des fichiers XML TEF dans le répertoire de sortie spécifié via {@see setOutputDir()}.
     *
     * @param string $thesesXmlFilepath Fichier XML source
     * @param string $xslFilepath Fichier XSL de transformation
     */
    public function transformToFiles(string $thesesXmlFilepath, string $xslFilepath)
    {
        // il faut spécifier un fichier mais c'est son répertoire qui recevra les fichiers générés
        $outputFilepath = $this->outputDir . '/dummy.xml';

        $this->saxonProcessor = new SaxonProcessor();
        $this->transformer = $this->saxonProcessor->newXslt30Processor();
        $this->transformer->setInitialMatchSelectionAsFile($thesesXmlFilepath);
        $this->transformer->applyTemplatesReturningFile($xslFilepath, $outputFilepath);
        $this->transformer->clearParameters();
        $this->transformer->clearProperties();
        unlink($outputFilepath);
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