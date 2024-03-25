<?php

namespace StepStar\Controller\Envoi;

use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;
use StepStar\Facade\Envoi\EnvoiFacade;
use StepStar\Form\Envoi\EnvoiFichiersForm;
use StepStar\Form\Envoi\EnvoiThesesForm;
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
         * @var \StepStar\Facade\Envoi\EnvoiFacade $envoiFacade
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
        /** @var EnvoiThesesForm $envoiThesesForm */
        $envoiThesesForm = $formElementsManager->get(EnvoiThesesForm::class);
        $controller->setEnvoiThesesForm($envoiThesesForm);
        /** @var \StepStar\Form\Envoi\EnvoiFichiersForm $envoiThesesForm */
        $envoiFichiersForm = $formElementsManager->get(EnvoiFichiersForm::class);
        $controller->setEnvoiFichiersForm($envoiFichiersForm);

        return $controller;
    }
}