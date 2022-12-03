<?php

namespace StepStar\Facade;

use Exception;
use Generator;
use Laminas\Stdlib\Glob;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use StepStar\Entity\Db\Log;
use StepStar\Exception\ApiServiceException;
use StepStar\Exception\TefServiceException;
use StepStar\Exception\XmlServiceException;
use StepStar\Service\Api\ApiServiceAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;
use StepStar\Service\Tef\TefServiceAwareTrait;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xsl\XslServiceAwareTrait;

class EnvoiFacade
{
    use LogServiceAwareTrait;
    use XslServiceAwareTrait;
    use XmlServiceAwareTrait;
    use TefServiceAwareTrait;
    use ApiServiceAwareTrait;

    use LoggerAwareTrait;

    // ATTENTION, la partie extension doit exprimer "la même chose" dans les 3 propriétés suivantes :
    private string $tefResultDocumentHref = '{$ETABLISSEMENT}_{THESE_ID}_{CODE_ETAB_SOUT}_{CODE_ETUDIANT}.tef.xml';
    private string $tefResultDocumentHrefTheseIdPregMatchPattern = '/^.+_(.+)_.+_.+\.tef\.xml$/U';
    private string $listXmlFilesInDirectoryGlobPattern = '*.tef.xml';

    private bool $saveLogs = true;

    /**
     * Active ou non l'enregistrement des logs en BDD.
     *
     * @param bool $saveLogs
     */
    public function setSaveLogs(bool $saveLogs): void
    {
        $this->saveLogs = $saveLogs;
    }

    /**
     * @param array $theses
     * @param bool $force
     * @param string $command
     * @return \Generator
     */
    public function envoyerTheses(array $theses, bool $force, string $command): Generator
    {
        $inputDir = sys_get_temp_dir() . '/' . uniqid('sygal_xml_input_');
        $outputDir = sys_get_temp_dir() . '/' . uniqid('sygal_xml_output_');

        $logTag = uniqid(); // tag commun à l'ensemble des logs produits ici

        /**
         * Generation du fichier XML intermediaire (1 pour N theses) & des fichiers TEF (1 par these).
         * (Un Log unique est créé pour cette opération.)
         */
        $operation = Log::OPERATION__GENERATION_XML;
        $this->newLog($operation, $command, $logTag);
        $success = true;
        try {
            $this->genererXmlForThesesInDir($theses, $inputDir);
            $this->generateTefFromDir($inputDir, $outputDir);
        } catch (Exception $e) {
            $this->appendExceptionToLog($e);
            $success = false;
        }
        $this->log->setSuccess($success);
        $this->saveCurrentLog();
        yield $this->log;

        if (!$success) {
            return;
        }

        /**
         * Envoi de chaque fichier TEF vers STEP/STAR, si nécessaire (i.e. si le TEF a changé depuis le dernier envoi).
         * (Un Log par thèse est créé.)
         */
        $operation = Log::OPERATION__ENVOI;
        $tefFilesPaths = $this->listXmlFilesInDirectory($outputDir);
        foreach ($tefFilesPaths as $i => $tefFilePath) {
            $theseId = $this->extractTheseIdFromTefFilePath($tefFilePath);
            $these = $theses[$theseId];
            $lastLog = $this->findLastLogForTheseAndOperation($theseId, $operation);
            $this->newLogForThese($theseId, $operation, $command, $logTag);
            $this->log->setTefFileContentHash(md5_file($tefFilePath));
            $success = true;
            $doctorantIdentite = $these['doctorant']['individu']['nomUsuel'] . ' ' . $these['doctorant']['individu']['prenom1'];
            $message = sprintf(
                "> Envoi %d/%d : These %d (%s) - Fichier '%s'",
                $i + 1, count($tefFilesPaths), $theseId, $doctorantIdentite, $tefFilePath
            );
            if ($force || $this->isEnvoiNecessaire($lastLog, $tefFilePath)) {
                $this->appendToLog($message);
                try {
                    $this->envoyer($tefFilePath);
                } catch (Exception $e) {
                    $success = false;
                    $this->appendToLog("  :-( " . $e->getMessage());
                    $this->log->setTefFileContent(file_get_contents($tefFilePath)); // conservation du TEF envoyé
                }
            } else {
                $this->appendToLog($message . ' - ' . sprintf(
                        "Inutile car identique au dernier envoi du %s.",
                        $lastLog->getStartedOnToString()
                    ));
            }
            $this->log->setSuccess($success);
            $this->saveCurrentLog();
            yield $this->log;
        }
    }

    private function saveCurrentLog()
    {
        if ($this->saveLogs) {
            $this->saveLog();
        }
    }

    private function extractTheseIdFromTefFilePath(string $tefFilePath): int
    {
        $result = preg_match($this->tefResultDocumentHrefTheseIdPregMatchPattern, basename($tefFilePath), $matches);
        if ($result !== 1) {
            throw new RuntimeException(
                "Impossible d'extraire l'id de la these a partir du nom du fichier TEF suivant : " . $tefFilePath
            );
        }

        return (int)$matches[1];
    }

    private function isEnvoiNecessaire(?Log $lastLog, string $tefFilePath): bool
    {
        if ($lastLog === null) {
            return true;
        }
        if (!$lastLog->isSuccess()) {
            return true;
        }
        if (!$lastLog->getTefFileContentHash()) {
            return true;
        }

        $tefFileContent = md5_file($tefFilePath);
        if ($tefFileContent !== $lastLog->getTefFileContentHash()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $theses Thèses à exporter au format XML.
     * @param string $outputDirPath Répertoire destination dans lequel générer le fichier XML
     * @return string Chemin du fichier XML généré
     * @throws \Exception
     */
    private function genererXmlForThesesInDir(array $theses, string $outputDirPath): string
    {
        if (!is_dir($outputDirPath)) {
            $ok = mkdir($outputDirPath);
            if (!$ok) {
                throw new Exception("Impossible de créer le répertoire '$outputDirPath'.");
            }
        }

        $xmlFilePath = $outputDirPath . '/' . uniqid('sygal_xml_') . '.xml';

        $this->generateXmlForThesesInFile($theses, $xmlFilePath);

        return $xmlFilePath;
    }

    /**
     * @param array $theses Thèses à exporter au format XML.
     * @param string $xmlFilePath Chemin du fichier XML à générer
     * @throws \Exception
     */
    private function generateXmlForThesesInFile(array $theses, string $xmlFilePath)
    {
        $theseIds = implode(',', array_map(fn($these) => $these['id'], $theses));

        $this->appendToLog(sprintf(
            "Generation du fichier XML intermediaire '%s' (1 seul pour toutes les theses) " .
            "pour les %d theses suivantes : %s :",
            $xmlFilePath, count($theses), $theseIds
        ));

        try {
            $this->xmlService->setTheses($theses);
            $this->xmlService->exportThesesToXml($xmlFilePath);
        } catch (XmlServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation du fichier XML.", null, $e);
        }

        if ($rejectedTheses = $this->xmlService->getRejectedTheses()) {
            $this->appendToLog('  :-( Les theses suivantes ont ete ecartees car elles sont invalides :');
            foreach ($rejectedTheses as $array) {
                $this->appendToLog(sprintf('      - %s : %s', $array['these']['id'], $array['reason']));
            }
        }

        if ($exceptions = $this->xmlService->getExceptions()) {
            foreach ($exceptions as $exception) {
                $this->appendToLog('  :-( ' . $exception->getMessage());
            }
        } else {
            $this->appendToLog("> " . realpath($xmlFilePath));
        }
    }

    /**
     * @throws \Exception
     */
    private function generateXmlForThese(array $these, string $xmlFilePath)
    {
        $theseId = $these['id'];
        $this->appendToLog("Generation du fichier XML contenant la these $theseId dans $xmlFilePath");
        try {
            $this->xmlService->setTheses([$these]);
            $this->xmlService->exportThesesToXml($xmlFilePath);
        } catch (XmlServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation du fichier XML.", null, $e);
        }
        if ($exceptions = $this->xmlService->getExceptions()) {
            foreach ($exceptions as $exception) {
                $this->appendToLog(':-( ' . $exception->getMessage());
            }
        } else {
            $this->appendToLog("> " . realpath($xmlFilePath));
        }
        $this->appendToLog("Terminee.");
    }

    /**
     * @throws \Exception
     */
    private function generateTefFromDir(string $inputDirPath, string $outputDir)
    {
        $this->appendToLog(sprintf(
            "Generation des fichiers TEF (1 par these) dans %s, a partir du repertoire %s :",
            $outputDir, $inputDirPath
        ));

//        $this->xslService->setOutputDir($inputDirPath);
        try {
            $xslFilePath = $this->xslService->generateXslFile($this->tefResultDocumentHref);
        } catch (TefServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation du fichier XSL.", null, $e);
        }

        $this->tefService->setOutputDir($outputDir);
        try {
            $this->tefService->generateTefFilesFromDir($inputDirPath, $xslFilePath);
        } catch (TefServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation des fichiers TEF.", null, $e);
        }

        $paths = $this->listXmlFilesInDirectory($outputDir);
        foreach ($paths as $path) {
            $this->appendToLog("> " . realpath($path));
        }
    }

    /**
     * @throws \Exception
     */
    private function generateTefFromFile(string $xmlInputFilePath, string $outputDir)
    {
        $this->appendToLog(sprintf(
            "Generation des fichiers TEF (1 par these) dans %s, a partir du fichier XML intermediaire %s :",
            $outputDir, $xmlInputFilePath
        ));

        $this->tefService->setOutputDir($outputDir);
        try {
            $xslFilePath = $this->xslService->generateXslFile($this->tefResultDocumentHref);
            $this->tefService->generateTefFilesFromFile($xmlInputFilePath, $xslFilePath);
        } catch (TefServiceException $e) {
            throw new Exception("Une erreur est survenue pendant la generation des fichiers TEF.", null, $e);
        }
        $paths = $this->listXmlFilesInDirectory($outputDir);
        foreach ($paths as $path) {
            $this->appendToLog("> " . realpath($path));
        }
    }

    /**
     * @throws \Exception
     */
    private function envoyer(string $tefFilePath, ?string $zipFilePath = null)
    {
        try {
            $this->apiService->deposer($tefFilePath, $zipFilePath);
        } catch (ApiServiceException $e) {
            if ($e->getResponse() !== null) {
                $message = $e->getMessage() . " Reponse complète du web service : " . $e->getResponse();
            } else {
                $message = "Une erreur est survenue lors de l'envoi. " . $e->getMessage();
            }

            throw new Exception($message, null, $e);
        }
    }

    /**
     * @param string $dir
     * @return array
     */
    private function listXmlFilesInDirectory(string $dir): array
    {
        return Glob::glob($dir . '/' . $this->listXmlFilesInDirectoryGlobPattern, Glob::GLOB_BRACE);
    }
}