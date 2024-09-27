<?php

namespace Application\Process\Utilisateur;

trait UtilisateurProcessAwareTrait
{
    protected UtilisateurProcess $utilisateurProcess;

    public function setUtilisateurProcess(UtilisateurProcess $utilisateurProcess): void
    {
        $this->utilisateurProcess = $utilisateurProcess;
    }
}