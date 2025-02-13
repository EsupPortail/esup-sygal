<?php

namespace Candidat\Service;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class CandidatServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return CandidatService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get('EtablissementService');

        $service = new CandidatService();

        $service->setEtablissementService($etablissementService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        return $service;
    }
}