<?php


namespace Application\Controller\Factory;

use Application\Controller\RapportAnnuelRechercheController;
use Application\Service\RapportAnnuel\RapportAnnuelSearchService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class RapportAnnuelRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var RapportAnnuelSearchService $searchService */
        $searchService = $container->getServiceLocator()->get(RapportAnnuelSearchService::class);

        $controller = new RapportAnnuelRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}