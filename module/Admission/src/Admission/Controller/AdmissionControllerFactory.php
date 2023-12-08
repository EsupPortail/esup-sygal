<?php
namespace Admission\Controller;

use Admission\Form\Admission\AdmissionForm;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Admission\Service\Verification\VerificationService;
use Application\Service\Discipline\DisciplineService;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;

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
        $structureService = $container->get(StructureService::class);
        $etudiantService = $container->get(EtudiantService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $verificationService = $container->get(VerificationService::class);
        $admissionService = $container->get(AdmissionService::class);
        $disciplineService = $container->get(DisciplineService::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $notifierService = $container->get(NotifierService::class);
        $documentService = $container->get(DocumentService::class);
        /** @var TypeValidationService $typeValidationService */
        $typeValidationService = $container->get(TypeValidationService::class);

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);

        /**
         * @var AdmissionForm $etudiantForm
         */
        $admissionForm = $container->get('FormElementManager')->get(AdmissionForm::class);

        $controller = new AdmissionController();
        $controller->setEntityManager($entityManager);
        $controller->setUserContextService($userContextService);
        $controller->setStructureService($structureService);
        $controller->setEtudiantService($etudiantService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setIndividuService($individuService);
        $controller->setTypeValidationService($typeValidationService);
        $controller->setFinancementService($financementService);
        $controller->setAdmissionValidationService($admissionValidationService);
        $controller->setVerificationService($verificationService);
        $controller->setAdmissionService($admissionService);
        $controller->setDisciplineService($disciplineService);
        $controller->setNotificationFactory($notificationFactory);
        $controller->setNotifierService($notifierService);
        $controller->setDocumentService($documentService);
        $controller->setAdmissionForm($admissionForm);
        $controller->setAdmissionOperationRule($admissionOperationRule);

        return $controller;
    }
}