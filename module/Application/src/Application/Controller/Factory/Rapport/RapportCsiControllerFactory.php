<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportCsiController;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use Application\Form\Rapport\RapportForm;
use Application\Form\RapportCsiForm;
use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Rapport\RapportService;
use Application\Service\Validation\ValidationService;
use ComiteSuiviIndividuel\Service\Membre\MembreService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class RapportCsiControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportCsiController
    {
        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportService        $rapportService
         * @var VersionFichierService $versionFichierService
         * @var IndividuService       $individuService
         * @var ValidationService     $validationService
         * @var RapportForm           $rapportForm
         * @var AnneeUnivService      $anneeUnivService
         *
         * @var MembreService $membreService
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportService = $container->get(RapportService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $individuService = $container->get(IndividuService::class);
        $rapportForm = $container->get('FormElementManager')->get(RapportCsiForm::class);
        $validationService = $container->get(ValidationService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_CSI);
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $membreService = $container->get(MembreService::class);

        $controller = new RapportCsiController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportForm);
        $controller->setValidationService($validationService);
        $controller->setAnneesUnivs($theseAnneeUnivService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);
        $controller->setMembreService($membreService);
        $controller->setAnneeUnivService($anneeUnivService);

//        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



