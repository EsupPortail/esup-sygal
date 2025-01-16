<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Formation\Form\Formation\FormationForm;
use Formation\Service\Formation\FormationService;
use Formation\Service\Module\ModuleService;
use Formation\Service\Notification\FormationNotificationFactory;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;

class FormationControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormationController
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var FormationService $formationService
         * @var ModuleService $moduleService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $formationService = $container->get(FormationService::class);
        $moduleService = $container->get(ModuleService::class);
        $sessionService = $container->get(SessionService::class);
        $notificationService = $container->get(NotifierService::class);
        /** @var FormationNotificationFactory $formationNotificationFactory */
        $formationNotificationFactory = $container->get(FormationNotificationFactory::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $natureFichier = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);

        /**
         * @var FormationForm $formationForm
         */
        $formationForm = $container->get('FormElementManager')->get(FormationForm::class);

        $controller = new FormationController();
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setFormationService($formationService);
        $controller->setModuleService($moduleService);
        $controller->setSessionService($sessionService);
        $controller->setFormationForm($formationForm);
        $controller->setNotifierService($notificationService);
        $controller->setFormationNotificationFactory($formationNotificationFactory);
        $controller->setAnneeUnivService($anneeUnivService);
        $controller->setFichierService($fichierService);
        $controller->setNatureFichierService($natureFichier);

        return $controller;
    }
}