<?php

namespace StepStar\Controller\Generate;

use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;
use StepStar\Facade\Generate\GenerateFacade;
use StepStar\Form\Generate\GenerateForm;
use StepStar\Service\Fetch\FetchService;

class GenerateControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GenerateController
    {
        $controller = new GenerateController();

        /** @var \StepStar\Facade\Generate\GenerateFacade $generateFacade */
        $generateFacade = $container->get(GenerateFacade::class);
        $controller->setGenerateFacade($generateFacade);
        /**
         * @var \StepStar\Service\Fetch\FetchService $fetchService
         */
        $fetchService = $container->get(FetchService::class);
        $controller->setFetchService($fetchService);

        /** @var FormElementManager $formElementsManager */
        $formElementsManager = $container->get(FormElementManager::class);
        /** @var \StepStar\Form\Generate\GenerateForm $envoiForm */
        $envoiForm = $formElementsManager->get(GenerateForm::class);
        $controller->setGenerateForm($envoiForm);

        return $controller;
    }
}