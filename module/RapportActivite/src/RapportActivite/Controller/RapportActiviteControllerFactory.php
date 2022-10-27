<?php

namespace RapportActivite\Controller;

use Application\Entity\Db\TypeValidation;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Rule\Televersement\RapportActiviteTeleversementRule;
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
         * @var \Fichier\Service\Fichier\FichierStorageService $fileService
         * @var \Fichier\Service\Fichier\FichierService $fichierService
         * @var \RapportActivite\Service\RapportActiviteService $rapportActiviteService
         * @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService
         * @var \RapportActivite\Service\Fichier\RapportActiviteFichierService $rapportActiviteFichierService
         * @var \RapportActivite\Form\RapportActiviteForm $rapportActiviteForm
         * @var \Application\Service\Validation\ValidationService $validationService
         * @var \These\Service\TheseAnneeUniv\TheseAnneeUnivService $theseAnneeUnivService
         */
        $fileService = $container->get(FichierStorageService::class);
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
        $controller->setFichierStorageService($fileService);
        $controller->setFichierService($fichierService);
        $controller->setRapportActiviteAvisService($rapportActiviteAvisService);
        $controller->setRapportActiviteFichierService($rapportActiviteFichierService);

        $controller->setForm($rapportActiviteForm);

        $controller->setTypeRapport($typeRapportActivite);
        $controller->setTypeValidation($typeValidation);

        /** @var \RapportActivite\Rule\Televersement\RapportActiviteTeleversementRule $rapportActiviteTeleversementRule */
        $rapportActiviteTeleversementRule = $container->get(RapportActiviteTeleversementRule::class);
        $rapportActiviteTeleversementRule->setAnneesUnivs([
            $theseAnneeUnivService->anneeUnivCourante(),
            $theseAnneeUnivService->anneeUnivPrecedente(),
        ]);
        $controller->setRapportActiviteTeleversementRule($rapportActiviteTeleversementRule);

        return $controller;
    }
}


