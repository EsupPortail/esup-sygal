<?php
namespace Admission\Controller\ConventionFormationDoctorale;

use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleForm;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Exporter\ConventionFormationDoctorale\ConventionFormationDoctoraleExporter;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;

class ConventionFormationDoctoraleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ConventionFormationDoctoraleController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConventionFormationDoctoraleController
    {
        $admissionService = $container->get(AdmissionService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $conventionFormationDoctoraleService = $container->get(ConventionFormationDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $conventionFormationDoctoraleExporter = $container->get(ConventionFormationDoctoraleExporter::class);
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);
        /**
         * @var ConventionFormationDoctoraleForm $conventionFormationDoctorale
         */
        $conventionFormationDoctorale = $container->get('FormElementManager')->get(ConventionFormationDoctoraleForm::class);

        $controller = new ConventionFormationDoctoraleController();
        $controller->setAdmissionService($admissionService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setConventionFormationDoctoraleService($conventionFormationDoctoraleService);
        $controller->setConventionFormationDoctoraleForm($conventionFormationDoctorale);
        $controller->setConventionFormationDoctoraleExporter($conventionFormationDoctoraleExporter);
        $controller->setEtablissementService($etablissementService);
        $controller->setAdmissionOperationRule($admissionOperationRule);


        return $controller;
    }
}