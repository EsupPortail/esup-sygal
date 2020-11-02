<?php

namespace Application\Service\Utilisateur;

use Interop\Container\ContainerInterface;

trait UtilisateurServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return UtilisateurService
     */
    public function locateUtilisateurService(ContainerInterface $container)
    {
        /** @var UtilisateurService $service */
        $service = $container->get('UtilisateurService');

        return $service;
    }
}