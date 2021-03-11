<?php

namespace Application\Service\Utilisateur;

trait UtilisateurServiceAwareTrait
{
    /**
     * @var UtilisateurService
     */
    protected $utilisateurService;

    /**
     * @param UtilisateurService $utilisateurService
     */
    public function setUtilisateurService(UtilisateurService $utilisateurService)
    {
        $this->utilisateurService = $utilisateurService;
    }

    /**
     * @return UtilisateurService
     */
    public function getUtilisateurService(): UtilisateurService
    {
        return $this->utilisateurService;
    }


}