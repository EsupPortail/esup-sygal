<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportAvisController;
use Application\Form\Rapport\RapportAvisForm;
use Application\Service\Rapport\Avis\RapportAvisService;
use Application\Service\Rapport\RapportService;
use Interop\Container\ContainerInterface;

class RapportAvisControllerFactory
{
    public function __invoke(ContainerInterface $container): RapportAvisController
    {
        $rapportService = $container->get(RapportService::class);
        $rapportAvisService = $container->get(RapportAvisService::class);
        $form = $container->get('FormElementManager')->get(RapportAvisForm::class);

        $controller = new RapportAvisController();
        $controller->setRapportService($rapportService);
        $controller->setRapportAvisService($rapportAvisService);
        $controller->setForm($form);

        return $controller;
    }
}