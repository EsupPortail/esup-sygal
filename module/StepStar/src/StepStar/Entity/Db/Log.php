<?php

namespace StepStar\Entity\Db;

use These\Entity\Db\These;
use DateTime;
use Exception;

class Log
{
    const OPERATION__ENVOI = 'ENVOI';
    const OPERATION__GENERATION_XML = 'GENERATION_XML';

    private int $id;
    private string $operation;
    private string $command;
    private ?string $tefFileContentHash;
    private ?string $tefFileContent;
    private bool $success;
    private string $log = '';
    private DateTime $startedOn;
    private DateTime $endedOn;
    private bool $hasProblems = false;

    private ?int $theseId = null;
    private ?These $these = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return string|null
     */
    public function getTefFileContentHash(): ?string
    {
        return $this->tefFileContentHash;
    }

    /**
     * @param string $tefFileContentHash
     */
    public function setTefFileContentHash(string $tefFileContentHash): void
    {
        $this->tefFileContentHash = $tefFileContentHash;
    }

    /**
     * @return string|null
     */
    public function getTefFileContent(): ?string
    {
        return $this->tefFileContent;
    }

    /**
     * @param string $tefFileContent
     */
    public function setTefFileContent(string $tefFileContent): void
    {
        $this->tefFileContent = $tefFileContent;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function isSuccessToString(): string
    {
        return $this->success ? 'Oui' : 'Non';
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * @return string
     */
    public function getTruncatedLog(): string
    {
        return mb_substr($this->log, 0, 120);
    }

    /**
     * @return string
     */
    public function getLogToHtml(): string
    {
        return str_replace(PHP_EOL, '<br>', htmlspecialchars($this->getLog()));
    }

    /**
     * @return string
     */
    public function getTruncatedLogToHtml(): string
    {
        return mb_substr($this->getTruncatedLog(), 0, 150);
    }

    /**
     * @param string $log
     */
    public function setLog(string $log): void
    {
        $this->log = $log;
    }

    /**
     * @param string $log
     */
    public function appendLog(string $log): void
    {
        $this->log .= $log;
    }

    /**
     * @param \Exception $e
     */
    public function appendException(Exception $e)
    {
        do {
            $this->appendLog($e->getMessage() . PHP_EOL);
            $this->appendLog($e->getTraceAsString() . PHP_EOL);
        } while (($e = $e->getPrevious()) !== null);
    }

    /**
     * @return \DateTime
     */
    public function getStartedOn(): DateTime
    {
        return $this->startedOn;
    }

    /**
     * @return string
     */
    public function getStartedOnToString(): string
    {
        return $this->startedOn->format('d/m/Y H:i:s');
    }

    /**
     * @param \DateTime|null $startedOn
     */
    public function setStartedOn(?DateTime $startedOn = null): void
    {
        $this->startedOn = $startedOn ?: date_create();
    }

    /**
     * @return \DateTime
     */
    public function getEndedOn(): DateTime
    {
        return $this->endedOn;
    }

    /**
     * @return string
     */
    public function getEndedOnToString(): string
    {
        return $this->endedOn->format('d/m/Y H:i:s');
    }

    /**
     * @param \DateTime|null $endedOn
     */
    public function setEndedOn(?DateTime $endedOn = null): void
    {
        $this->endedOn = $endedOn ?: date_create();
    }

    /**
     * Retourne la durée en minutes et seconds.
     *
     * @return string Ex : '12m23s'
     */
    public function getDurationToString(): string
    {
        $interval = $this->endedOn->diff($this->startedOn);

        return ($interval->h * 60 * 60 + $interval->i * 60 + $interval->s) . "s";
    }

    /**
     * @return bool
     */
    public function hasProblems(): bool
    {
        return $this->hasProblems;
    }

    /**
     * @param bool $hasProblems
     */
    public function setHasProblems(bool $hasProblems): void
    {
        $this->hasProblems = $hasProblems;
    }

    /**
     * @return int|null
     */
    public function getTheseId(): ?int
    {
        return $this->these ? $this->these->getId() : $this->theseId;
    }

    /**
     * @return \These\Entity\Db\These|null
     */
    public function getThese(): ?These
    {
        return $this->these;
    }

    /**
     * @param int $theseId
     */
    public function setTheseId(int $theseId): void
    {
        $this->theseId = $theseId;
    }

    /**
     * @param \These\Entity\Db\These $these
     */
    public function setThese(These $these): void
    {
        $this->these = $these;
    }
}
