<?php
namespace Admission\Controller;

use Admission\Form\Admission\AdmissionForm;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Document\DocumentServiceFactory;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Individu\IndividuService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Validation\ValidationService;
use Application\Service\Discipline\DisciplineService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\NatureFichier\NatureFichierService;
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
        $structureService = $container->get(StructureService::class);
        $individuAdmissionService = $container->get(IndividuService::class);
        $individuService = $container->get(\Individu\Service\IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $validationService = $container->get(ValidationService::class);
        $admissionService = $container->get(AdmissionService::class);
        $disciplineService = $container->get(DisciplineService::class);
        $notificationFactory = $container->get(NotificationFactory::class);
        $notifierService = $container->get(NotifierService::class);
        $natureFichierService = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);
        $documentService = $container->get(DocumentService::class);

        /**
         * @var AdmissionForm $etudiantForm
         */
        $admissionForm = $container->get('FormElementManager')->get(AdmissionForm::class);

        $controller = new AdmissionController();

        $controller->setStructureService($structureService);
        $controller->setIndividuAdmissionService($individuAdmissionService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setIndividuService($individuService);
        $controller->setFinancementService($financementService);
        $controller->setValidationService($validationService);
        $controller->setAdmissionService($admissionService);
        $controller->setDisciplineService($disciplineService);
        $controller->setNotificationFactory($notificationFactory);
        $controller->setNotifierService($notifierService);
        $controller->setNatureFichierService($natureFichierService);
        $controller->setFichierService($fichierService);
        $controller->setDocumentService($documentService);
        $controller->setAdmissionForm($admissionForm);

        return $controller;
    }
}