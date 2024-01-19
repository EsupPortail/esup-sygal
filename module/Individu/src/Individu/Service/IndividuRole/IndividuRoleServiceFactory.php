<?php

namespace Individu\Service\IndividuRole;

use Interop\Container\ContainerInterface;

class IndividuRoleServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuRoleService
    {
        $service = new IndividuRoleService();
//
//        /** @var ProfilService $profilService */
//        $profilService = $container->get(ProfilService::class);
//        $service->setProfilService($profilService);

        return $service;
    }
}
