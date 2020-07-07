<?php

namespace Application\Controller\Factory;

use Application\Controller\ListeDiffusionController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\Notification\NotifierService;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class ListeDiffusionControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();

        $controller = new ListeDiffusionController();

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $controller->setEtablissementService($etablissementService);

        /**
         * @var ListeDiffusionService $listeDiffusionService
         */
        $listeDiffusionService = $container->get(ListeDiffusionService::class);
        $controller->setListeDiffusionService($listeDiffusionService);

        /**
         * @var FileService $fileService
         */
        $fileService = $container->get(FileService::class);
        $controller->setFileService($fileService);

        /**
         * @var NotifierService $notifierService
         */
        $notifierService = $container->get(NotifierService::class);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}