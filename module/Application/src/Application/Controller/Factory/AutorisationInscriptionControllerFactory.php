<?php

namespace Application\Controller\Factory;

use Application\Controller\AutorisationInscriptionController;
use Application\Form\AutorisationInscriptionForm;
use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\AutorisationInscription\AutorisationInscriptionService;
use Application\Service\Rapport\RapportService;
use Interop\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class AutorisationInscriptionControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AutorisationInscriptionController
    {
        /**
         * @var TheseService          $theseService
         * @var RapportService        $rapportService
         * @var AnneeUnivService      $anneeUnivService
         * @var AutorisationInscriptionService $autorisationInscriptionService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         */
        $theseService = $container->get('TheseService');
        $rapportService = $container->get(RapportService::class);
        $autorisationInscriptionService = $container->get(AutorisationInscriptionService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $form = $container->get('FormElementManager')->get(AutorisationInscriptionForm::class);

        $controller = new AutorisationInscriptionController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setAnneesUnivs($theseAnneeUnivService);
        $controller->setAutorisationInscriptionForm($form);
        $controller->setAutorisationInscriptionService($autorisationInscriptionService);

        return $controller;
    }
}



