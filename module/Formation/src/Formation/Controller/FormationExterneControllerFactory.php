<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Formation\Form\Formation\FormationExterne\FormationExterneForm;
use Formation\Service\Formation\FormationExterne\FormationExterneService;
use Formation\Service\Notification\FormationNotificationFactory;
use Formation\Service\Validation\ValidationFormationExterne\ValidationFormationExterneService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormationExterneControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationExterneController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormationExterneController
    {
        /**
         * @var EntityManager $entityManager
         * @var NatureFichierService $natureFichier
         * @var FichierService $fichierService
         * @var FichierStorageService $fichierStorageService
         * @var FormationExterneService $formationExterneService
         * @var ValidationFormationExterneService $validationFormationExterneService
         * @var FormationNotificationFactory $formationNotificationFactory
         * @var NotifierService $notifierService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $natureFichier = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $formationExterneService = $container->get(FormationExterneService::class);
        $validationFormationExterneService = $container->get(ValidationFormationExterneService::class);
        $formationNotificationFactory = $container->get(FormationNotificationFactory::class);
        $notifierService = $container->get(NotifierService::class);

        /**
         * @var FormationExterneForm $formationExterneForm
         */
        $formationExterneForm = $container->get('FormElementManager')->get(FormationExterneForm::class);

        $controller = new FormationExterneController();
        $controller->setEntityManager($entityManager);
        $controller->setFormationExterneForm($formationExterneForm);
        $controller->setFichierService($fichierService);
        $controller->setNatureFichierService($natureFichier);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setFormationExterneService($formationExterneService);
        $controller->setValidationFormationExterneService($validationFormationExterneService);
        $controller->setFormationNotificationFactory($formationNotificationFactory);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}