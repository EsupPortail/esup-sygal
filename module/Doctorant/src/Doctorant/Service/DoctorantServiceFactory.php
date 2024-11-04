<?php

namespace Doctorant\Service;

use Application\Service\Source\SourceService;
use Individu\Service\IndividuService;
use Structure\Service\Etablissement\EtablissementService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class DoctorantServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return DoctorantService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get('EtablissementService');

        $service = new DoctorantService();

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