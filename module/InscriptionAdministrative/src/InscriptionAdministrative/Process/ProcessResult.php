<?php

namespace InscriptionAdministrative\Process;

use Exception;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;

class ProcessResult implements MessageAwareInterface
{
    use MessageAwareTrait;

    protected bool $success;
    protected ?Exception $exception = null;

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    public function setException(?Exception $exception): self
    {
        $this->exception = $exception;
        return $this;
    }
}