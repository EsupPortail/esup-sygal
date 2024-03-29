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
     *
     * @param string|null $operation
     * @param string $command
     * @param string|null $tag
     */
    protected function newLog(?string $operation = null, string $command = '', ?string $tag = null)
    {
        $this->log = $this->logService->newLog($operation, $command, $tag);
    }

    /**
     * Instancie un nouveau Log concernant une thèse, qui devient le Log courant.
     *
     * @param int $theseId
     * @param string $operation
     * @param string $command
     * @param string|null $tag
     */
    protected function newLogForThese(int $theseId, string $operation, string $command, ?string $tag = null)
    {
        $this->log = $this->logService->newLogForThese($theseId, $operation, $command, $tag);
    }

    /**
     * @param int $theseId
     * @param string $operation
     * @return \StepStar\Entity\Db\Log|null
     */
    protected function findLastLogForTheseAndOperation(int $theseId, string $operation): ?Log
    {
        return $this->logService->findLastLogForTheseAndOperation($theseId, $operation);
    }

    /**
     * Ajoute le texte spécifié au contenu du Log courant.
     *
     * @param string $text
     */
    protected function appendToLog(string $text)
    {
        $this->log->appendLog($text . PHP_EOL);
    }

    /**
     * Ajoute au contenu du Log courant le message et la trace de l'exception spécifiée (et de ses prédécesseures).
     *
     * @param \Exception $e
     */
    protected function appendExceptionToLog(Exception $e)
    {
        $this->log->appendException($e);
    }

    /**
     * Enregistre le Log courant en bdd avec le statut spécifié.
     */
    protected function saveLog()
    {
        $this->logService->saveLog($this->log);
    }

    /**
     * @param \StepStar\Service\Log\LogService $logService
     */
    public function setLogService(LogService $logService): void
    {
        $this->logService = $logService;
    }
}