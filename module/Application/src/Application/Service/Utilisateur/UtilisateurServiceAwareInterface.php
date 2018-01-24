<?php

namespace Application\Service\Utilisateur;

interface UtilisateurServiceAwareInterface
{
    public function setUtilisateurService(UtilisateurService $utilisateurService);
}