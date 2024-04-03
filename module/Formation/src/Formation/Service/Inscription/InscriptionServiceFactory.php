<?php

namespace Formation\Service\Inscription;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class InscriptionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : InscriptionService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $anneeUnivService =  $container->get(AnneeUnivService::class);

        $service = new InscriptionService();
        $service->setEntityManager($entitymanager);
        $service->setAnneeUnivService($anneeUnivService);
        return $service;
    }
}