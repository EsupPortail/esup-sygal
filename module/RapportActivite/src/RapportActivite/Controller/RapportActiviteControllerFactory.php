<?php

namespace RapportActivite\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Validation\ValidationService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Psr\Container\ContainerInterface;
use RapportActivite\Form\RapportActiviteAnnuelForm;
use RapportActivite\Form\RapportActiviteFinContratForm;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
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
        $controller = new RapportActiviteController();

        // services
        /** @var \Fichier\Service\Fichier\FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);
        $controller->setFichierStorageService($fileService);
        /** @var \Fichier\Service\Fichier\FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);
        $controller->setFichierService($fichierService);
        /** @var \RapportActivite\Service\RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $controller->setRapportActiviteService($rapportActiviteService);
        /** @var \RapportActivite\Service\Fichier\RapportActiviteFichierService $rapportActiviteFichierService */
        $rapportActiviteFichierService = $container->get(RapportActiviteFichierService::class);
        $controller->setRapportActiviteFichierService($rapportActiviteFichierService);

        // forms
        /** @var \RapportActivite\Form\RapportActiviteAnnuelForm $rapportActiviteAnnuelForm */
        $rapportActiviteAnnuelForm = $container->get('FormElementManager')->get(RapportActiviteAnnuelForm::class);
        /** @var \RapportActivite\Form\RapportActiviteFinContratForm $rapportActiviteFinContratForm */
        $rapportActiviteFinContratForm = $container->get('FormElementManager')->get(RapportActiviteFinContratForm::class);
        $controller->setAnnuelForm($rapportActiviteAnnuelForm);
        $controller->setFinContratForm($rapportActiviteFinContratForm);

        // rules
        /** @var \RapportActivite\Rule\Creation\RapportActiviteCreationRule $rapportActiviteCreationRule */
        $rapportActiviteCreationRule = $container->get(RapportActiviteCreationRule::class);
        $controller->setRapportActiviteCreationRule($rapportActiviteCreationRule);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $controller->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \Application\Service\Validation\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $controller->setValidationService($validationService);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $controller->setAnneeUnivService($anneeUnivService);

        return $controller;
    }
}



