<?php

namespace Soutenance\Service\Membre;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use UnicaenAuthToken\Service\TokenService;

class MembreServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return MembreService
     */
    public function __invoke(ContainerInterface $container) : MembreService
    {
        /**
         * @var EntityManager $entityManager
         * @var QualiteService $qualiteService
         * @var TokenService $tokenService
         * @var UserContextService $userContextService
         * @var UtilisateurService $utilisateurService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $qualiteService = $container->get(QualiteService::class);
        $tokenService = $container->get(TokenService::class);
        $userContextService = $container->get('UserContextService');
        $utilisateurService = $container->get(UtilisateurService::class);

        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setQualiteService($qualiteService);
        $service->setTokenService($tokenService);
        $service->setUserContextService($userContextService);
        $service->setUtilisateurService($utilisateurService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $service->setActeurTheseService($acteurService);

        /** @var ActeurHDRService $acteurHDRService */
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $service->setActeurHDRService($acteurHDRService);

        return $service;
    }
}
