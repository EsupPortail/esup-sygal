<?php

namespace Application\Service\Utilisateur;

use Zend\ServiceManager\ServiceLocatorInterface;

trait UtilisateurServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return UtilisateurService
     */
    public function locateUtilisateurService(ServiceLocatorInterface $sl)
    {
        /** @var UtilisateurService $service */
        $service = $sl->get('UtilisateurService');

        return $service;
    }
}