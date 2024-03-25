<?php

namespace StepStar\Facade\Envoi;

use Exception;
use Generator;
use Laminas\Stdlib\Glob;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use StepStar\CleanableAfterWorkTrait;
use StepStar\Entity\Db\Log;
use StepStar\Exception\ApiServiceException;
use StepStar\Facade\Generate\GenerateFacadeAwareTrait;
use StepStar\Facade\TefFileNameParamsAwareTrait;
use StepStar\Service\Api\ApiServiceAwareTrait;
use StepStar\Service\Log\LogServiceAwareTrait;

class EnvoiFacade
{
    use LogServiceAwareTrait;
    use ApiServiceAwareTrait;
    use GenerateFacadeAwareTrait;
    use TefFileNameParamsAwareTrait;
    use LoggerAwareTrait;
    use CleanableAfterWorkTrait;

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
     * @param string $tefInputDirPath Chemin absolu du répertoire contenant les fichiers TEF à envoyer
     * @return \Generator
     */
    public function envoyerFichiers(string $tefInputDirPath): Generator
    {
        /**
         * Envoi de chaque fichier TEF vers STEP/STAR, si nécessaire (i.e. si le TEF a changé depuis le dernier envoi).
         * (Un Log par thèse est créé.)
         */
        $operation = Log::OPERATION__ENVOI;
        $nbEnvoisReussis = $nbEnvoisEchoues = 0;
        $tefFilesPaths = $this->listTefFilesInDirectory($tefInputDirPath);
        foreach ($tefFilesPaths as $i => $tefFilePath) {
            $this->newLog($operation);
            $this->log->setTefFileContentHash(md5_file($tefFilePath));
            $success = true;
            $message = sprintf(
                "> Envoi %d/%d : Fichier '%s'",
                $i + 1, count($tefFilesPaths), $tefFilePath
            );
            $this->appendToLog($message);
            try {
                $this->envoyer($tefFilePath);
                $nbEnvoisReussis++;
            } catch (Exception $e) {
                $nbEnvoisEchoues++;
                $success = false;
                $this->appendToLog("  :-( " . $e->getMessage());
                $this->log->setTefFileContent(file_get_contents($tefFilePath)); // conservation du TEF envoyé
            }
            $this->log->setSuccess($success);
            yield $this->log;
        }

        $operation = Log::OPERATION__SYNTHESE;
        $this->newLog($operation);
        $this->log->setSuccess(true);
        $message = sprintf(
            "Synthèse des envois : %d envois prévus / %d envois réussis / %d envois échoués.",
            count($tefFilesPaths), $nbEnvoisReussis, $nbEnvoisEchoues
        );
        $this->appendToLog($message);
        yield $this->log;
    }

    /**
     * @param array $theses Thèses à envoyer
     * @param bool $force Forcer l'envoi même si le contenu du TEF est identique au précédent envoi ?
     * @param string $command Commande ayant déclenché l'envoi, inscrite dans les logs
     * @param string|null $tag Eventuel tag commun à l'ensemble des logs qui seront produits
     * @return \Generator
     */
    public function envoyerTheses(array $theses, bool $force, string $command, ?string $tag = null): Generator
    {
        /**
         * Generation du fichier XML intermediaire (1 pour N theses) & des fichiers TEF (1 par these).
         */
        $logs = $this->generateFacade->generateFilesForTheses($theses, $command, $tag);
        /** @var \StepStar\Entity\Db\Log $log */
        foreach ($logs as $log) {
            $this->log = $log;
            $this->saveCurrentLog();
            yield $this->log;
        }
        if (!$this->generateFacade->isSuccess()) {
            return;
        }
        $outputDir = $this->generateFacade->getOutputDirPath();
        $tefOutputDir = $this->generateFacade->getTefOutputDirPath();

        /**
         * Envoi de chaque fichier TEF vers STEP/STAR, si nécessaire (i.e. si le TEF a changé depuis le dernier envoi).
         * (Un Log par thèse est créé.)
         */
        $operation = Log::OPERATION__ENVOI;
        $nbEnvoisReussis = $nbEnvoisEchoues = $nbEnvoisInutiles = 0;
        $tefFilesPaths = $this->listTefFilesInDirectory($tefOutputDir);
        foreach ($tefFilesPaths as $i => $tefFilePath) {
            $theseId = $this->extractTheseIdFromTefFilePath($tefFilePath);
            $these = $theses[$theseId];
            $lastLog = $this->findLastLogForTheseAndOperation($theseId, $operation);
            $this->newLogForThese($theseId, $operation, $command, $tag);
            $this->log->setTefFileContentHash(md5_file($tefFilePath));
            $success = true;
            $doctorantIdentite = $these['doctorant']['individu']['nomUsuel'] . ' ' . $these['doctorant']['individu']['prenom1'];
            if ($force || $this->isEnvoiNecessaire($lastLog, $tefFilePath)) {
                $message = sprintf(
                    "> Envoi %d/%d : These %d (%s) - Fichier '%s'",
                    $i + 1, count($tefFilesPaths), $theseId, $doctorantIdentite, $tefFilePath
                );
                $this->appendToLog($message);
                try {
                    $this->envoyer($tefFilePath);
                    $nbEnvoisReussis++;
                } catch (Exception $e) {
                    $nbEnvoisEchoues++;
                    $success = false;
                    $this->appendToLog("  :-( " . $e->getMessage());
                    $this->log->setTefFileContent(file_get_contents($tefFilePath)); // conservation du TEF envoyé
                }
            } else {
                $nbEnvoisInutiles++;
                $message = sprintf(
                    "> Envoi %d/%d inutile : These %d (%s) - Fichier '%s' - Inutile car identique au dernier envoi du %s.",
                    $i + 1, count($tefFilesPaths), $theseId, $doctorantIdentite, $tefFilePath, $lastLog->getStartedOnToString()
                );
                $this->appendToLog($message);
            }
            $this->log->setSuccess($success);
            $this->saveCurrentLog();
            yield $this->log;
        }

        $operation = Log::OPERATION__SYNTHESE;
        $this->newLog($operation, $command, $tag);
        $this->log->setSuccess(true);
        $message = sprintf(
            "Synthèse des envois : %d envois prévus / %d envois réussis / %d envois échoués / %d envois inutiles.",
            count($tefFilesPaths), $nbEnvoisReussis, $nbEnvoisEchoues, $nbEnvoisInutiles
        );
        $this->appendToLog($message);
        if ($this->cleanAfterWork) {
            $this->appendToLog("Suppression des fichiers/repertoires de travail : ");
            $this->appendToLog("> " . $outputDir);
            exec('rm -r ' . escapeshellarg($outputDir));
        }
        $this->saveCurrentLog();
        yield $this->log;
    }

    private function saveCurrentLog(): void
    {
        if ($this->saveLogs) {
            $this->saveLog();
        }
    }

    private function extractTheseIdFromTefFilePath(string $tefFilePath): int
    {
        $result = preg_match($this->tefResultDocumentHrefTheseIdPregMatchPattern, basename($tefFilePath), $matches);
        if ($result !== 1) {
            throw new RuntimeException(sprintf(
                "Impossible d'extraire du nom de fichier '%s' l'identifiant de la these (motif utilisé : %s). " .
                "Voici un exemple de nom de fichier exploitable : %s",
                $tefFilePath, $this->tefResultDocumentHrefTheseIdPregMatchPattern, $this->tefResultDocumentHrefExample
            ));
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
     * @throws \Exception
     */
    private function envoyer(string $tefFilePath, ?string $zipFilePath = null): void
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

    private function listTefFilesInDirectory(string $dir): array
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            throw new RuntimeException("Le répertoire suivant n'existe pas ou n'est pas accessible : " . $dir);
        }

        $paths = Glob::glob($dir . '/' . $this->listTefFilesInDirectoryGlobPattern, Glob::GLOB_BRACE);

        if (count($paths) === 0) {
            throw new RuntimeException(sprintf(
                "Aucun fichier TEF n'a été trouvé dans le répertoire '%s' avec le motif suivant : %s",
                $dir, $this->listTefFilesInDirectoryGlobPattern
            ));
        }

        return $paths;
    }

    public function getTefFilesGlobPattern(): string
    {
        return $this->listTefFilesInDirectoryGlobPattern;
    }
}