<?php

namespace Application\Entity\Db;

trait AppPseudoUtilisateurAwareTrait
{
    /**
     * @var Utilisateur
     */
    protected $appPseudoUtilisateur;

    /**
     * @param Utilisateur $appPseudoUtilisateur
     */
    public function setAppPseudoUtilisateur(Utilisateur $appPseudoUtilisateur)
    {
        $this->appPseudoUtilisateur = $appPseudoUtilisateur;
    }
}