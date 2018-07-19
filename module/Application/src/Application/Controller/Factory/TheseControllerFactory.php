<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Service\Workflow\WorkflowService;
use Doctrine\ORM\EntityManager;
use Import\Service\ImportService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TheseController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $options = $this->getOptions($controllerManager->getServiceLocator());

        /**
         * @var VariableService $variableService
         * @var ValidationService $validationService
         * @var VersionFichierService $versionFichierService
         * @var TheseService $theseService
         * @var TheseRechercheService $theseRechercheService
         * @var RoleService $roleService
         * @var FichierService $fichierService
         * @var WorkflowService $workflowService
         * @var NotifierService $notifierService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var MailConfirmationService $mailConfirmationService
         * @var EntityManager $entityManager
         * @var ImportService $importService
         */
        $variableService = $controllerManager->getServiceLocator()->get('VariableService');
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $versionFichierService = $controllerManager->getServiceLocator()->get('VersionFichierService');
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $theseRechercheService = $controllerManager->getServiceLocator()->get('TheseRechercheService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $uniteService = $controllerManager->getServiceLocator()->get('UniteRechercheService');
        $fichierService = $controllerManager->getServiceLocator()->get('FichierService');
        $workflowService = $controllerManager->getServiceLocator()->get('WorkflowService');
        $etablissementService = $controllerManager->getServiceLocator()->get('EtablissementService');
        $mailConfirmationService = $controllerManager->getServiceLocator()->get('MailConfirmationService');
        $entityManager = $controllerManager->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $importService = $controllerManager->getServiceLocator()->get('ImportService');

        $controller = new TheseController();
        $controller->setTimeoutRetraitement($this->getTimeoutRetraitementFromOptions($options));
        $controller->setVariableService($variableService);
        $controller->setValidationService($validationService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setTheseService($theseService);
        $controller->setTheseRechercheService($theseRechercheService);
        $controller->setRoleService($roleService);
        $controller->setFichierService($fichierService);
        $controller->setWorkflowService($workflowService);
        $controller->setEtablissementService($etablissementService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setEntityManager($entityManager);
        $controller->setNotifierService($notifierService);
        $controller->setImportService($importService);

        return $controller;
    }

    private function getTimeoutRetraitementFromOptions(array $options)
    {
        return isset($options['retraitement']['timeout']) ? $options['retraitement']['timeout'] : null;
    }

    private function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('config');

        return isset($options['sygal']) ? $options['sygal'] : [];
    }
}