<?php

namespace Formation\Service\Formation\FormationExterne;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormationExterneServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationExterneService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormationExterneService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $service = new FormationExterneService();
        $service->setEntityManager($entitymanager);
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}