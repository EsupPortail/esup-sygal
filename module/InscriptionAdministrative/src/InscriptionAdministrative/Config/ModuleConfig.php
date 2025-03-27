<?php

namespace InscriptionAdministrative\Config;

use InscriptionAdministrative\Config\Reception\ReceptionConfig;
use Laminas\Stdlib\AbstractOptions;

class ModuleConfig extends AbstractOptions
{
    private bool $runReceptionProcessAfterImport = false;

    private ReceptionConfig $reception;

    /**
     * Active ou non l'automaticité du traitement de l'IA (par le service {@see InscriptionAdministrativeReceptionProcess})
     * lorsque l'événement {@see InscriptionAdministrativeImportEvent} est reçu.
     *
     * Clé de config : 'run_reception_process_after_import'
     */
    public function setRunReceptionProcessAfterImport(bool $runReceptionProcessAfterImport): void
    {
        $this->runReceptionProcessAfterImport = $runReceptionProcessAfterImport;
    }

    /**
     * Retourne la valeur du témoin d'automaticité du traitement de l'IA (par le service {@see InscriptionAdministrativeReceptionProcess})
     * lorsque l'événement {@see InscriptionAdministrativeImportEvent} est reçu.
     *
     * Clé de config : 'run_reception_process_after_import'
     */
    public function getRunReceptionProcessAfterImport(): bool
    {
        return $this->runReceptionProcessAfterImport;
    }

    /**
     * Spécifie la config concernant la réception (traitement) d'IA.
     *
     * Clé de config : 'reception'.
     */
    public function setReception(array $reception): void
    {
        $this->reception = new ReceptionConfig($reception);
    }

    /**
     * Retourne la config concernant la réception (traitement) d'IA.
     *
     * Clé de config : 'reception'.
     */
    public function getReception(): ReceptionConfig
    {
        return $this->reception;
    }
}