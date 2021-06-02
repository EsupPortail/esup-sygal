<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use Application\Form\Rapport\RapportForm;
use Application\Form\RapportMiparcoursForm;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\Rapport\RapportService;
use Application\Service\These\TheseService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\Validation\ValidationService;
use Application\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;

class RapportMiparcoursControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportMiparcoursController
     */
    public function __invoke(ContainerInterface $container): RapportMiparcoursController
    {
        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportService        $rapportService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var ValidationService     $validationService
         * @var RapportForm           $rapportForm
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportService = $container->get(RapportService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get('IndividuService');
        $rapportForm = $container->get('FormElementManager')->get(RapportMiparcoursForm::class);
        $validationService = $container->get(ValidationService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_MIPARCOURS);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_MIPARCOURS);

        $controller = new RapportMiparcoursController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportForm);
        $controller->setValidationService($validationService);
        $controller->setTheseAnneeUnivService($theseAnneeUnivService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);

        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



