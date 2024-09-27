<?php

namespace Application\Service\Utilisateur;

use Application\Service\Source\SourceService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\User as UserService;

class UtilisateurServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UtilisateurService
    {
        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        /** @var UserService $userService */
        $userService = $container->get('unicaen-auth_user_service');

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);

        $service = new UtilisateurService();

        $service->setUserService($userService);
        $service->setSourceService($sourceService);
        $service->setIndividuService($individuService);

        return $service;
    }
}
