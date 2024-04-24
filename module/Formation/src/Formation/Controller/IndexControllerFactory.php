<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndexController
    {
        /**
         * @var EntityManager $entityManager
         * @var DoctorantService $doctorantService
         * @var ParametreService $parametreService
         * @var AnneeUnivService $anneeUnivService
        */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $parametreService = $container->get(ParametreService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $controller = new IndexController();
        $controller->setEntityManager($entityManager);
        $controller->setDoctorantService($doctorantService);
        $controller->setParametreService($parametreService);
        $controller->setAnneeUnivService($anneeUnivService);

        return $controller;
    }

}