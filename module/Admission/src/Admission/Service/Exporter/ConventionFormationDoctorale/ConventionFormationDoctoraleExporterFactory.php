<?php

namespace Admission\Service\Exporter\ConventionFormationDoctorale;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\Url\UrlService;
use Application\Service\Role\RoleService;
use Fichier\Service\Fichier\FichierStorageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use UnicaenRenderer\Service\Rendu\RenduService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

class ConventionFormationDoctoraleExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ConventionFormationDoctoraleExporter
    {
        /**
         * @var EtablissementService $etablissementService
         * @var FichierStorageService $fichierStorageService
         * @var RenduService $renduService
         * @var StructureService $structureService
         * @var UrlService $urlService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $renduService = $container->get(RenduService::class);
        $structureService = $container->get(StructureService::class);
        $urlService = $container->get(UrlService::class);
        $admissionService = $container->get(AdmissionService::class);
        $roleService = $container->get(RoleService::class);
        $renderer = $container->get('ViewRenderer');

        $exporter = new ConventionFormationDoctoraleExporter($renderer, 'A4');
        $exporter->setEtablissementService($etablissementService);
        $exporter->setFichierStorageService($fichierStorageService);
        $exporter->setRenduService($renduService);
        $exporter->setStructureService($structureService);
        $exporter->setUrlService($urlService);
        $exporter->setAdmissionService($admissionService);
        $exporter->setApplicationRoleService($roleService);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $exporter->setTemplateVariablePluginManager($rapm);

        return $exporter;
    }
}