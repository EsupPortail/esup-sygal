<?php
namespace Admission\Controller;

use Admission\Form\Admission\AdmissionForm;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Validation\ValidationService;
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
        $structureService = $container->get(StructureService::class);
        $etudiantService = $container->get(EtudiantService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $validationService = $container->get(ValidationService::class);
        $verificationService = $container->get(VerificationService::class);
        $admissionService = $container->get(AdmissionService::class);
        $disciplineService = $container->get(DisciplineService::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $notifierService = $container->get(NotifierService::class);
        $natureFichierService = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);
        $documentService = $container->get(DocumentService::class);
        $versionFichierService = $container->get(VersionFichierService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);


        /**
         * @var AdmissionForm $etudiantForm
         */
        $admissionForm = $container->get('FormElementManager')->get(AdmissionForm::class);

        $controller = new AdmissionController();
        $controller->setEntityManager($entityManager);
        $controller->setStructureService($structureService);
        $controller->setEtudiantService($etudiantService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setIndividuService($individuService);
        $controller->setFinancementService($financementService);
        $controller->setValidationService($validationService);
        $controller->setVerificationService($verificationService);
        $controller->setAdmissionService($admissionService);
        $controller->setDisciplineService($disciplineService);
        $controller->setNotificationFactory($notificationFactory);
        $controller->setNotifierService($notifierService);
        $controller->setNatureFichierService($natureFichierService);
        $controller->setFichierService($fichierService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setDocumentService($documentService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setAdmissionForm($admissionForm);

        return $controller;
    }
}