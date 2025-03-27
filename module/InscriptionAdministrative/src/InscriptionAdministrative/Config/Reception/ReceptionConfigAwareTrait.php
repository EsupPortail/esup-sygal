<?php

namespace InscriptionAdministrative\Config\Reception;

trait ReceptionConfigAwareTrait
{
    protected ReceptionConfig $receptionConfig;

    public function setReceptionConfig(ReceptionConfig $receptionConfig): void
    {
        $this->receptionConfig = $receptionConfig;
    }
}