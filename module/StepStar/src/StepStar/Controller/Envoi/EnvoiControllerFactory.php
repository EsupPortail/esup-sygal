<?php

namespace StepStar\Controller\Envoi;

use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;
use StepStar\Facade\EnvoiFacade;
use StepStar\Form\EnvoiForm;
use StepStar\Service\Fetch\FetchService;

class EnvoiControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EnvoiController
    {
        $controller = new EnvoiController();

        /**
         * @var \StepStar\Facade\EnvoiFacade $envoiFacade
         */
        $envoiFacade = $container->get(EnvoiFacade::class);
        $controller->setEnvoiFacade($envoiFacade);

        /**
         * @var \StepStar\Service\Fetch\FetchService $fetchService
         */
        $fetchService = $container->get(FetchService::class);
        $controller->setFetchService($fetchService);

        /** @var FormElementManager $formElementsManager */
        $formElementsManager = $container->get(FormElementManager::class);
        /** @var EnvoiForm $envoiForm */
        $envoiForm = $formElementsManager->get(EnvoiForm::class);
        $controller->setEnvoiForm($envoiForm);

        return $controller;
    }
}