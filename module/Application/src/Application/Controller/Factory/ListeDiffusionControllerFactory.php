<?php

namespace Application\Controller\Factory;

use Application\Controller\ListeDiffusionController;
use Application\Service\File\FileService;
use Application\Service\Individu\IndividuService;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\Notification\NotifierService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class ListeDiffusionControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $container = $container->getServiceLocator();

        $controller = new ListeDiffusionController();

        /**
         * @var ListeDiffusionService $individuService
         */
        $individuService = $container->get(ListeDiffusionService::class);
        $controller->setListeDiffusionService($individuService);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get('IndividuService');
        $controller->setIndividuService($individuService);

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