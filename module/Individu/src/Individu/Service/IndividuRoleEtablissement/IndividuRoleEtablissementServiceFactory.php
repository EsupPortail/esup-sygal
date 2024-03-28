<?php

namespace Individu\Service\IndividuRoleEtablissement;

use Interop\Container\ContainerInterface;

class IndividuRoleEtablissementServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuRoleEtablissementService
    {
        $service = new IndividuRoleEtablissementService();
//
//        /** @var ProfilService $profilService */
//        $profilService = $container->get(ProfilService::class);
//        $service->setProfilService($profilService);

        return $service;
    }
}
