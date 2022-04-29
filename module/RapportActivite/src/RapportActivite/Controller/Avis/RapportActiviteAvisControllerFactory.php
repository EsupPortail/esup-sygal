<?php

namespace RapportActivite\Controller\Avis;

use Application\Service\Notification\NotifierService;
use Application\Service\Validation\ValidationService;
use Laminas\EventManager\EventManagerInterface;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRule;
use RapportActivite\Rule\Validation\RapportActiviteValidationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use UnicaenAvis\Form\AvisForm;

class RapportActiviteAvisControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisController
    {
        $rapportService = $container->get(RapportActiviteService::class);
        $rapportAvisService = $container->get(RapportActiviteAvisService::class);
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);
        $rapportValidationService = $container->get(RapportActiviteValidationService::class);
        $validationService = $container->get(ValidationService::class);
        $notifierService = $container->get(NotifierService::class);

        $rapportActiviteAvisNotificationRule = $container->get(RapportActiviteAvisNotificationRule::class);
        $rapportActiviteValidationRule = $container->get(RapportActiviteValidationRule::class);

        $controller = new RapportActiviteAvisController();
        $controller->setRapportActiviteService($rapportService);
        $controller->setRapportActiviteAvisService($rapportAvisService);
        $controller->setRapportActiviteValidationService($rapportValidationService);
        $controller->setValidationService($validationService);
        $controller->setNotifierService($notifierService);

        $controller->setForm($avisForm);

        $controller->setNotificationRule($rapportActiviteAvisNotificationRule);
        $controller->setValidationRule($rapportActiviteValidationRule);

        $controller->setEventManager($container->get('EventManager'));

        return $controller;
    }
}