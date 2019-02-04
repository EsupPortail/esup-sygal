<?php

namespace Application\Service\Individu;

use Application\Service\Utilisateur\UtilisateurService;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndividuServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $sl->get('UtilisateurService');
        $utilisateur = $utilisateurService->getRepository()->fetchAppPseudoUtilisateur();

        $service = new IndividuService();
        $service->setAppPseudoUtilisateur($utilisateur);

        return $service;
    }
}