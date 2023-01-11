<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportCsiController;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use Application\Form\Rapport\RapportForm;
use Application\Form\RapportCsiForm;
use ComiteSuiviIndividuel\Service\Membre\MembreService;
use Fichier\Service\Fichier\FichierService;
use Individu\Service\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\Rapport\RapportService;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\Validation\ValidationService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;

class RapportCsiControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportCsiController
     */
    public function __invoke(ContainerInterface $container): RapportCsiController
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
         *
         * @var MembreService $membreService
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportService = $container->get(RapportService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get(IndividuService::class);
        $rapportForm = $container->get('FormElementManager')->get(RapportCsiForm::class);
        $validationService = $container->get(ValidationService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_CSI);

        $membreService = $container->get(MembreService::class);

        $controller = new RapportCsiController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setApplicationNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportForm);
        $controller->setValidationService($validationService);
        $controller->setAnneesUnivs($theseAnneeUnivService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);
        $controller->setMembreService($membreService);

//        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



