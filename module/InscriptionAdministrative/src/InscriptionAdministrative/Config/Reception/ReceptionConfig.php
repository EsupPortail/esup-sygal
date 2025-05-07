<?php

namespace InscriptionAdministrative\Config\Reception;

use Laminas\Stdlib\AbstractOptions;

class ReceptionConfig extends AbstractOptions
{
    private bool $updateDoctorantIfPossible;

    /**
     * Autorise ou non la mise à jour du doctorant (à partir des données "apprenant") *lorsque c'est possible*.
     * NB : même si l'autorisation n'est pas accordée, le Code Apprenant du doctorant sera tout de même mis à jour.
     */
    public function setUpdateDoctorantIfPossible(bool $updateDoctorantIfPossible = true): void
    {
        $this->updateDoctorantIfPossible = $updateDoctorantIfPossible;
    }

    /**
     * Indique si la mise à jour du doctorant (à partir des données "apprenant") *lorsque c'est possible* est autorisée.
     */
    public function getUpdateDoctorantIfPossible(): bool
    {
        return $this->updateDoctorantIfPossible;
    }
}