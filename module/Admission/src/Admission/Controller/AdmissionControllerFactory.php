<?php
namespace Admission\Controller;

use Admission\Form\Admission\AdmissionForm;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionRechercheService;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Exporter\Admission\AdmissionExporter;
use Admission\Service\Exporter\Recapitulatif\RecapitulatifExporter;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Operation\AdmissionOperationService;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Admission\Service\Verification\VerificationService;
use Application\Service\Role\RoleService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenRenderer\Service\Rendu\RenduService;

class AdmissionControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AdmissionController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AdmissionController
    {
        /**
         * @var NotificationFactory $notificationFactory
         * @var NotifierService $notifierService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $etudiantService = $container->get(EtudiantService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $verificationService = $container->get(VerificationService::class);
        $admissionService = $container->get(AdmissionService::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $notifierService = $container->get(NotifierService::class);
        $documentService = $container->get(DocumentService::class);
        $recapitulatifExporter = $container->get(RecapitulatifExporter::class);
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $admissionOperationService = $container->get(AdmissionOperationService::class);
        $conventionFormationDoctoraleService = $container->get(ConventionFormationDoctoraleService::class);
        $roleService = $container->get(RoleService::class);
        $admissionRechercheService = $container->get(AdmissionRechercheService::class);
        $admissionExporter = $container->get(AdmissionExporter::class);
        $admissionTypeValidationService = $container->get(TypeValidationService::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $renduService = $container->get(RenduService::class);

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);

        /**
         * @var AdmissionForm $etudiantForm
         */
        $admissionForm = $container->get('FormElementManager')->get(AdmissionForm::class);

        $controller = new AdmissionController();
        $controller->setEntityManager($entityManager);
        $controller->setUserContextService($userContextService);
        $controller->setEtudiantService($etudiantService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setIndividuService($individuService);
        $controller->setFinancementService($financementService);
        $controller->setVerificationService($verificationService);
        $controller->setAdmissionService($admissionService);
        $controller->setNotificationFactory($notificationFactory);
        $controller->setNotifierService($notifierService);
        $controller->setDocumentService($documentService);
        $controller->setEtablissementService($etablissementService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setRecapitulatifExporter($recapitulatifExporter);
        $controller->setAdmissionForm($admissionForm);
        $controller->setAdmissionOperationRule($admissionOperationRule);
        $controller->setAdmissionOperationService($admissionOperationService);
        $controller->setConventionFormationDoctoraleService($conventionFormationDoctoraleService);
        $controller->setApplicationRoleService($roleService);
        $controller->setAdmissionRechercheService($admissionRechercheService);
        $controller->setAdmissionExporter($admissionExporter);
        $controller->setTypeValidationService($admissionTypeValidationService);
        $controller->setAdmissionValidationService($admissionValidationService);
        $controller->setRenduService($renduService);

        return $controller;
    }
}