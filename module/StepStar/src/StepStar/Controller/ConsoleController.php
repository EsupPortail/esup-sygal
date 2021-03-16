<?php

namespace StepStar\Controller;

use StepStar\Exception\ApiServiceException;
use StepStar\Exception\XmlServiceException;
use StepStar\Exception\XslServiceException;
use StepStar\Exception\ZipServiceException;
use StepStar\Service\Api\ApiServiceAwareTrait;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xsl\XslServiceAwareTrait;
use StepStar\Service\Zip\ZipServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Stdlib\Glob;

class ConsoleController extends AbstractConsoleController
{
    use XmlServiceAwareTrait;
    use XslServiceAwareTrait;
    use ApiServiceAwareTrait;
    use ZipServiceAwareTrait;

    /**
     * @var string
     */
    protected $outputDir;

    public function genererXmlAction()
    {
        $id = $this->params()->fromRoute('these');
        $xmlFilePath = $this->params()->fromRoute('to');
        $anonymize = $this->params()->fromRoute('anonymize', false);
        $this->console->writeLine("# Génération du fichier XML contenant la thèse $id dans $xmlFilePath");
        try {
            $this->xmlService->exportTheseToXml($id, $xmlFilePath, $anonymize);
        } catch (XmlServiceException $e) {
            throw new RuntimeException("Une erreur est survenue pendant la génération du fichier XML.", null, $e);
        }
        $this->console->writeLine("> " . realpath($xmlFilePath));
        $this->console->writeLine("Terminée.");
    }

    public function genererTefAction()
    {
        $xmlFilePath = $this->params()->fromRoute('from');
        $outputDir = $this->params()->fromRoute('dir') ?: sys_get_temp_dir() . '/' . uniqid('out_');
        $this->console->writeLine("# Génération des fichiers TEF dans $outputDir à partir de $xmlFilePath");
        try {
            $this->xslService->transformToFiles($xmlFilePath, $outputDir);
        } catch (XslServiceException $e) {
            throw new RuntimeException("Une erreur est survenue pendant la génération des fichiers TEF.", null, $e);
        }
        $paths = $this->listXmlFilesInDirectory($outputDir);
        foreach ($paths as $path) {
            $this->console->writeLine("> " . realpath($path));
        }
        $this->console->writeLine("Terminée.");
    }

    public function genererZipAction()
    {
        $id = $this->params()->fromRoute('these');
        $this->console->writeLine("# Création de l'archive Zip contenant les fichiers de la thèse $id");
        try {
            $zipFilePath = $this->zipService->compresserFichiersThese($id);
        } catch (ZipServiceException $e) {
            throw new RuntimeException("Une erreur est survenue pendant la création de l'archive.", null, $e);
        }
        $this->console->writeLine("> " . realpath($zipFilePath));
        $this->console->writeLine("Terminée.");
    }

    public function deposerAction()
    {
        $tefFilePath = $this->params()->fromRoute('tef');
        $zipFilePath = $this->params()->fromRoute('zip');
        $this->console->writeLine("# Export vers STAR du fichier TEF $tefFilePath");
        if ($zipFilePath !== null) {
            $this->console->writeLine("# et du fichier ZIP $zipFilePath");
        }
        try {
            $this->apiService->deposer($tefFilePath, $zipFilePath);
        } catch (ApiServiceException $e) {
            $message = "Une erreur est survenue pendant l'export vers STAR.";
            if ($e->getResponse() !== null) {
                $message .= PHP_EOL . " Réponse reçue du web service : " . PHP_EOL . $e->getResponse();
            }
            throw new RuntimeException($message, null, $e);
        }
        $this->console->writeLine("Terminé.");
    }

    /**
     * @param string $dir
     * @return array
     */
    private function listXmlFilesInDirectory(string $dir): array
    {
        return Glob::glob($dir . '/*.xml', Glob::GLOB_BRACE);
    }
}