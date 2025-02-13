<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Entity\Db\TypeRapport;
use Application\Form\Rapport\RapportForm;
use Application\Form\RapportMiparcoursForm;
use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Rapport\RapportService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationThese\ValidationTheseService;
use Validation\Service\ValidationService;

class RapportMiparcoursControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportMiparcoursController
    {
        /** @var ValidationService $validationTheseService */
        $validationTheseService = $container->get(ValidationService::class);
        $typeValidation = $validationTheseService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_MIPARCOURS);

        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportService        $rapportService
         * @var VersionFichierService $versionFichierService
         * @var IndividuService       $individuService
         * @var ValidationTheseService     $validationTheseService
         * @var RapportForm           $rapportForm
         * @var AnneeUnivService      $anneeUnivService
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportService = $container->get(RapportService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $individuService = $container->get(IndividuService::class);
        $rapportForm = $container->get('FormElementManager')->get(RapportMiparcoursForm::class);
        $validationTheseService = $container->get(ValidationTheseService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_MIPARCOURS);
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $controller = new RapportMiparcoursController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportForm);
        $controller->setValidationTheseService($validationTheseService);
        $controller->setAnneeUnivService($anneeUnivService);
        $controller->setAnneesUnivs($theseAnneeUnivService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);

//        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



