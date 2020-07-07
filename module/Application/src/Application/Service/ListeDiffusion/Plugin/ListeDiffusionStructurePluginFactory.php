<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Service\Acteur\ActeurService;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class ListeDiffusionStructurePluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new ListeDiffusionStructurePlugin();

        $config = $container->get('Config');
        $service->setConfig($config['sygal']['liste-diffusion'] ?? []);

        /**
         * @var ActeurService $acteurService
         */
        $acteurService = $container->get(ActeurService::class);
        $service->setActeurService($acteurService);

        /**
         * @var DoctorantService $doctorantService
         */
        $doctorantService = $container->get(DoctorantService::class);
        $service->setDoctorantService($doctorantService);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get('IndividuService');
        $service->setIndividuService($individuService);

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}