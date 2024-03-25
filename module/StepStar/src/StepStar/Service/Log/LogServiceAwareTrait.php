<?php

namespace StepStar\Service\Log;

use Exception;
use StepStar\Entity\Db\Log;

trait LogServiceAwareTrait
{
    protected LogService $logService;
    protected ?Log $log = null;

    /**
     * Instancie un nouveau Log tout nu, qui devient le Log courant.
     */
    protected function newLog(?string $operation = null, string $command = '', ?string $tag = null): void
    {
        $this->log = $this->logService->newLog($operation, $command, $tag);
    }

    /**
     * Instancie un nouveau Log concernant une thèse, qui devient le Log courant.
     */
    protected function newLogForThese(int $theseId, string $operation, string $command, ?string $tag = null): void
    {
        $this->log = $this->logService->newLogForThese($theseId, $operation, $command, $tag);
    }

    protected function findLastLogForTheseAndOperation(int $theseId, string $operation): ?Log
    {
        return $this->logService->findLastLogForTheseAndOperation($theseId, $operation);
    }

    /**
     * Ajoute le texte spécifié au contenu du Log courant.
     */
    protected function appendToLog(string $text): void
    {
        $this->log->appendLog($text . PHP_EOL);
    }

    /**
     * Ajoute au contenu du Log courant le message et la trace de l'exception spécifiée (et de ses prédécesseures).
     */
    protected function appendExceptionToLog(Exception $e): void
    {
        $this->log->appendException($e);
    }

    /**
     * Enregistre le Log courant en bdd avec le statut spécifié.
     */
    protected function saveLog(): void
    {
        $this->logService->saveLog($this->log);
    }

    public function setLogService(LogService $logService): void
    {
        $this->logService = $logService;
    }
}