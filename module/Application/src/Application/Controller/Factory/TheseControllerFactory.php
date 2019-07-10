<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\FichierThese\FichierTheseService;
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
use Application\SourceCodeStringHelper;
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
        $sl = $controllerManager->getServiceLocator();

        $options = $this->getOptions($sl);

        /**
         * @var VariableService         $variableService
         * @var ValidationService       $validationService
         * @var VersionFichierService   $versionFichierService
         * @var TheseService            $theseService
         * @var TheseRechercheService   $theseRechercheService
         * @var RoleService             $roleService
         * @var FichierTheseService     $fichierTheseService
         * @var WorkflowService         $workflowService
         * @var NotifierService         $notifierService
         * @var EtablissementService    $etablissementService
         * @var UniteRechercheService   $uniteService
         * @var MailConfirmationService $mailConfirmationService
         * @var EntityManager           $entityManager
         * @var ImportService           $importService
         */
        $variableService = $sl->get('VariableService');
        $validationService = $sl->get('ValidationService');
        $versionFichierService = $sl->get('VersionFichierService');
        $theseService = $sl->get('TheseService');
        $theseRechercheService = $sl->get('TheseRechercheService');
        $roleService = $sl->get('RoleService');
        $uniteService = $sl->get('UniteRechercheService');
        $fichierTheseService = $sl->get('FichierTheseService');
        $workflowService = $sl->get('WorkflowService');
        $etablissementService = $sl->get('EtablissementService');
        $mailConfirmationService = $sl->get('MailConfirmationService');
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');
        $notifierService = $sl->get(NotifierService::class);
        $importService = $sl->get('ImportService');

        $controller = new TheseController();
        $controller->setTimeoutRetraitement($this->getTimeoutRetraitementFromOptions($options));
        $controller->setVariableService($variableService);
        $controller->setValidationService($validationService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setTheseService($theseService);
        $controller->setTheseRechercheService($theseRechercheService);
        $controller->setRoleService($roleService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setWorkflowService($workflowService);
        $controller->setEtablissementService($etablissementService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setEntityManager($entityManager);
        $controller->setNotifierService($notifierService);
        $controller->setImportService($importService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

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