<?php

namespace InscriptionAdministrative\Process\Reception;

trait ReceptionProcessAwareTrait
{
    protected ReceptionProcess $receptionProcess;

    public function setReceptionProcess(ReceptionProcess $receptionProcess): void
    {
        $this->receptionProcess = $receptionProcess;
    }
}