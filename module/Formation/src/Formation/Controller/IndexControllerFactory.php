<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Formation\Service\Justificatif\JustificatifService;
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
         * @var JustificatifService $justificatifService
        */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $parametreService = $container->get(ParametreService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $justificatifService = $container->get(JustificatifService::class);

        $controller = new IndexController();
        $controller->setEntityManager($entityManager);
        $controller->setDoctorantService($doctorantService);
        $controller->setParametreService($parametreService);
        $controller->setAnneeUnivService($anneeUnivService);
        $controller->setJustificatifService($justificatifService);

        return $controller;
    }

}