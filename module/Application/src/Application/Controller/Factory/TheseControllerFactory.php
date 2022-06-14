<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseController;
use Application\Form\AttestationTheseForm;
use Application\Form\DiffusionTheseForm;
use Application\Form\MetadonneeTheseForm;
use Application\Form\PointsDeVigilanceForm;
use Application\Form\RdvBuTheseDoctorantForm;
use Application\Form\RdvBuTheseForm;
use Application\Service\Acteur\ActeurService;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\File\FileService;
use Application\Service\Individu\IndividuService;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Service\Workflow\WorkflowService;
use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Import\Service\ImportService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class TheseControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseController
     */
    public function __invoke(ContainerInterface $container)
    {
        $options = $this->getOptions($container);

        /**
         * @var VariableService         $variableService
         * @var ValidationService       $validationService
         * @var VersionFichierService   $versionFichierService
         * @var TheseService            $theseService
         * @var RoleService             $roleService
         * @var FichierTheseService     $fichierTheseService
         * @var FileService             $fileService
         * @var WorkflowService         $workflowService
         * @var NotifierService         $notifierService
         * @var EtablissementService    $etablissementService
         * @var UniteRechercheService   $uniteService
         * @var MailConfirmationService $mailConfirmationService
         * @var EntityManager           $entityManager
         * @var ImportService           $importService
         * @var UtilisateurService      $utilisateurService
         * @var ActeurService           $acteurService
         * @var IndividuService         $indivdiService
         */
        $variableService = $container->get('VariableService');
        $validationService = $container->get('ValidationService');
        $versionFichierService = $container->get('VersionFichierService');
        $theseService = $container->get('TheseService');
        $roleService = $container->get('RoleService');
        $uniteService = $container->get('UniteRechercheService');
        $fichierTheseService = $container->get('FichierTheseService');
        $fileService = $container->get(FileService::class);
        $workflowService = $container->get('WorkflowService');
        $etablissementService = $container->get('EtablissementService');
        $mailConfirmationService = $container->get('MailConfirmationService');
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $notifierService = $container->get(NotifierService::class);
//        $importService = $container->get('ImportService');
        $utilisateurService = $container->get('UtilisateurService');

        /**
         * @var RdvBuTheseDoctorantForm $rdvBuTheseDoctorantForm
         * @var RdvBuTheseForm $rdvBuTheseForm
         */
        $rdvBuTheseDoctorantForm = $container->get('FormElementManager')->get('RdvBuTheseDoctorantForm');
        $rdvBuTheseForm = $container->get('FormElementManager')->get('RdvBuTheseForm');

        /**
         * @var AttestationTheseForm $attestationTheseForm
         * @var DiffusionTheseForm $diffusionTheseForm
         * @var MetadonneeTheseForm $metadonneeTheseForm
         * @var PointsDeVigilanceForm $pointsDeVigilanceForm
         */
        $attestationTheseForm = $container->get('FormElementManager')->get('AttestationTheseForm');
        $diffusionTheseForm = $container->get('FormElementManager')->get('DiffusionTheseForm');
        $metadonneeTheseForm = $container->get('FormElementManager')->get('MetadonneeTheseForm');
        $pointsDeVigilanceForm = $container->get('FormElementManager')->get('PointsDeVigilanceForm');

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new TheseController();
        $controller->setTimeoutRetraitement($this->getTimeoutRetraitementFromOptions($options));
        $controller->setVariableService($variableService);
        $controller->setValidationService($validationService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setTheseService($theseService);
        $controller->setRoleService($roleService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFileService($fileService);
        $controller->setWorkflowService($workflowService);
        $controller->setEtablissementService($etablissementService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setEntityManager($entityManager);
        $controller->setNotifierService($notifierService);
//        $controller->setImportService($importService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setRdvBuTheseDoctorantForm($rdvBuTheseDoctorantForm);
        $controller->setRdvBuTheseForm($rdvBuTheseForm);
        $controller->setAttestationTheseForm($attestationTheseForm);
        $controller->setDiffusionTheseForm($diffusionTheseForm);
        $controller->setMetadonneeTheseForm($metadonneeTheseForm);
        $controller->setPointsDeVigilanceForm($pointsDeVigilanceForm);
        $controller->setRenderer($renderer);
        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }

    private function getTimeoutRetraitementFromOptions(array $options)
    {
        return isset($options['retraitement']['timeout']) ? $options['retraitement']['timeout'] : null;
    }

    private function getOptions(ContainerInterface $container)
    {
        $options = $container->get('config');

        return isset($options['sygal']) ? $options['sygal'] : [];
    }
}