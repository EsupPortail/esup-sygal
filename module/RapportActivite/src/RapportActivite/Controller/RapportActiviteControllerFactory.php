<?php

namespace RapportActivite\Controller;

use Application\Entity\Db\TypeValidation;
use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Fichier\RapportActiviteFichierService;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     *
     * @return RapportActiviteController
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteController
    {
        /**
         * @var \Application\Service\File\FileService $fileService
         * @var \Application\Service\Fichier\FichierService $fichierService
         * @var \RapportActivite\Service\RapportActiviteService $rapportActiviteService
         * @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService
         * @var \RapportActivite\Service\Fichier\RapportActiviteFichierService $rapportActiviteFichierService
         * @var \RapportActivite\Form\RapportActiviteForm $rapportActiviteForm
         * @var \Application\Service\Validation\ValidationService $validationService
         * @var \Application\Service\TheseAnneeUniv\TheseAnneeUnivService $theseAnneeUnivService
         */
        $fileService = $container->get(FileService::class);
        $fichierService = $container->get(FichierService::class);
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $rapportActiviteFichierService = $container->get(RapportActiviteFichierService::class);
        $rapportActiviteForm = $container->get('FormElementManager')->get(RapportActiviteForm::class);
        $validationService = $container->get(ValidationService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);

        $typeRapportActivite = $rapportActiviteService->findTypeRapport();
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        $controller = new RapportActiviteController();
        $controller->setRapportActiviteService($rapportActiviteService);
        $controller->setFileService($fileService);
        $controller->setFichierService($fichierService);
        $controller->setRapportActiviteAvisService($rapportActiviteAvisService);
        $controller->setRapportActiviteFichierService($rapportActiviteFichierService);
        $controller->setForm($rapportActiviteForm);
        $controller->setTheseAnneeUnivService($theseAnneeUnivService);

        $controller->setTypeRapport($typeRapportActivite);
        $controller->setTypeValidation($typeValidation);

        return $controller;
    }
}



