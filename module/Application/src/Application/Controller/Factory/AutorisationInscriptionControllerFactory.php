<?php

namespace Application\Controller\Factory;

use Application\Controller\AutorisationInscriptionController;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use Application\Form\AutorisationInscriptionForm;
use Application\Form\Rapport\RapportAvisForm;
use Application\Form\Rapport\RapportForm;
use Application\Form\RapportCsiForm;
use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\AutorisationInscription\AutorisationInscriptionService;
use Application\Service\Rapport\RapportService;
use Application\Service\Source\SourceService;
use Application\Service\Validation\ValidationService;
use ComiteSuiviIndividuel\Service\Membre\MembreService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
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
        $rapportService = $container->get(RapportService::class);
        $individuService = $container->get(IndividuService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $sourceService = $container->get(SourceService::class);
        $rapportService = $container->get(RapportService::class);
        $autorisationInscriptionService = $container->get(AutorisationInscriptionService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $form = $container->get('FormElementManager')->get(AutorisationInscriptionForm::class);

        $membreService = $container->get(MembreService::class);

        $controller = new AutorisationInscriptionController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setIndividuService($individuService);
        $controller->setAnneeUnivService($anneeUnivService);
//        $controller->setTypeRapport($typeRapport);
        $controller->setMembreService($membreService);
        $controller->setSourceService($sourceService);
        $controller->setRapportService($rapportService);
        $controller->setAnneesUnivs($theseAnneeUnivService);
        $controller->setAutorisationInscriptionForm($form);
        $controller->setAutorisationInscriptionService($autorisationInscriptionService);

//        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



