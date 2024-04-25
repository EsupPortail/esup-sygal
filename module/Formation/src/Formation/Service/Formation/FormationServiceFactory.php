<?php

namespace Formation\Service\Formation;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormationServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormationService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $service = new FormationService();
        $service->setEntityManager($entitymanager);
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}